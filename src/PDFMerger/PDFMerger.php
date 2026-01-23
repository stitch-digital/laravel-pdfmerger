<?php

declare(strict_types=1);

/*
* File:     PDFMerger.php
* Category: PDFMerger
* Author:   M. Goldenbaum
* Created:  01.12.16 20:18
* Updated:  -
*
* Description:
*  -
*/

namespace StitchDigital\PDFMerger;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use setasign\Fpdi\Fpdi as FPDI;
use setasign\Fpdi\PdfParser\StreamReader;
use StitchDigital\PDFMerger\Enums\Orientation;
use StitchDigital\PDFMerger\Exceptions\InvalidPagesException;
use StitchDigital\PDFMerger\Exceptions\PDFMergeException;
use StitchDigital\PDFMerger\Exceptions\PDFNotFoundException;

class PDFMerger
{
    use Conditionable;
    use Macroable;
    use Tappable;

    protected Filesystem $filesystem;

    protected FPDI $fpdi;

    /**
     * @var Collection<int, array{name: string, pages: string|array<int>, orientation: Orientation|string|null}>
     */
    protected Collection $files;

    /**
     * @var Collection<int, string>
     */
    protected Collection $tmpFiles;

    protected string $fileName = 'undefined.pdf';

    protected Orientation|string|null $defaultOrientation = null;

    protected bool $duplexMode = false;

    /**
     * Track whether merge has been performed
     */
    protected bool $merged = false;

    /**
     * Construct and initialize a new instance
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->fpdi = new FPDI;
        $this->files = collect([]);
        $this->tmpFiles = collect([]);

        // Load configuration defaults
        $this->defaultOrientation = config('pdfmerger.orientation');
        $this->duplexMode = config('pdfmerger.duplex', false);
    }

    /**
     * Static factory method for fluent instantiation (Laravel convention)
     */
    public static function make(): self
    {
        return app(self::class);
    }

    /**
     * Static factory alias (Filament pattern)
     */
    public static function new(): self
    {
        return static::make();
    }

    /**
     * Initialize a new internal instance of FPDI in order to prevent any problems with shared resources
     * Please visit https://www.setasign.com/products/fpdi/manual/#p-159 for more information on this issue
     *
     * @deprecated Use make() instead
     */
    public function init(): self
    {
        $this->fpdi = new FPDI;
        $this->files = collect([]);

        return $this;
    }

    /**
     * Reset the merger instance to its initial state
     */
    public function reset(): self
    {
        $this->fpdi = new FPDI;
        $this->files = collect([]);
        $this->fileName = 'undefined.pdf';
        $this->defaultOrientation = config('pdfmerger.orientation');
        $this->duplexMode = config('pdfmerger.duplex', false);
        $this->merged = false;

        return $this;
    }

    /**
     * The class deconstructor method
     */
    public function __destruct()
    {
        $filesystem = $this->filesystem;
        $this->tmpFiles->each(function ($filePath) use ($filesystem) {
            if ($filesystem->exists($filePath)) {
                $filesystem->delete($filePath);
            }
        });
    }

    /**
     * Set the default orientation for all pages
     */
    public function orientation(Orientation|string $orientation): self
    {
        $this->defaultOrientation = $orientation;

        return $this;
    }

    /**
     * Enable or disable duplex mode
     */
    public function duplex(bool $enabled = true): self
    {
        $this->duplexMode = $enabled;

        return $this;
    }

    /**
     * Normalize a URL by removing extra whitespace and encoding spaces
     */
    protected function normalizeUrl(string $url): string
    {
        // Trim whitespace from the entire URL
        $url = trim($url);

        // Replace multiple consecutive spaces with a single space
        $normalized = preg_replace('/\s+/', ' ', $url);

        // Handle case where preg_replace returns null
        if ($normalized === null) {
            return $url;
        }

        $url = $normalized;

        // Encode spaces and other special characters in the URL
        // We need to be careful to only encode the path part, not the scheme/host
        if (preg_match('/^(https?:\/\/[^\/]+)(.*)$/i', $url, $matches)) {
            $baseUrl = $matches[1];
            $path = $matches[2];

            // Encode spaces in the path
            $path = str_replace(' ', '%20', $path);

            return $baseUrl.$path;
        }

        return $url;
    }

    /**
     * Check if a string is a URL
     */
    protected function isUrl(string $path): bool
    {
        // Normalize the path first for validation
        $normalizedPath = $this->normalizeUrl($path);

        return (bool) filter_var($normalizedPath, FILTER_VALIDATE_URL) &&
               preg_match('/^https?:\/\//i', $normalizedPath);
    }

    /**
     * Download a PDF from a URL to a temporary file
     *
     * @throws PDFNotFoundException if the URL cannot be downloaded
     */
    protected function downloadUrl(string $url): string
    {
        // Store original URL for debugging
        $originalUrl = $url;

        // Normalize the URL to handle whitespace and encoding issues
        $url = $this->normalizeUrl($url);

        // Check if URL downloads are allowed
        if (! config('pdfmerger.allow_urls', true)) {
            throw new PDFNotFoundException("URL downloads are disabled in configuration. Cannot download from '$url'");
        }

        $tempPath = config('pdfmerger.temp_path', storage_path('tmp/pdfmerger'));

        // Ensure temp directory exists
        if (! $this->filesystem->exists($tempPath)) {
            $this->filesystem->makeDirectory($tempPath, 0755, true);
        }

        // Generate unique filename
        $filePath = $tempPath.'/'.Str::random(16).'.pdf';

        try {
            // Set up context options for file_get_contents
            $timeout = config('pdfmerger.url_download_timeout', 30);
            $verifySSL = config('pdfmerger.url_verify_ssl', true);

            // Automatically disable SSL verification in local development environments
            // This makes it work with .test, .local domains and self-signed certificates
            if (app()->environment('local')) {
                $verifySSL = false;
            }

            $contextOptions = [
                'http' => [
                    'timeout' => $timeout,
                    'follow_location' => true,
                    'max_redirects' => 5,
                ],
                'ssl' => [
                    'verify_peer' => $verifySSL,
                    'verify_peer_name' => $verifySSL,
                ],
            ];

            $context = stream_context_create($contextOptions);
            $content = @file_get_contents($url, false, $context);

            if ($content === false) {
                $error = error_get_last();
                $errorMessage = $error['message'] ?? 'Unknown error';

                // Check for SSL certificate errors
                $isSSLError = str_contains($errorMessage, 'SSL') ||
                             str_contains($errorMessage, 'certificate') ||
                             str_contains($errorMessage, 'crypto');

                if ($isSSLError) {
                    $envInfo = app()->environment('local')
                        ? 'SSL verification is already disabled for local environments.'
                        : 'You can disable SSL verification by setting APP_ENV=local or PDFMERGER_VERIFY_SSL=false in your .env file.';

                    $sslHelp = "\n\nSSL Error Detected:\n".
                              "The server's SSL certificate could not be verified.\n".
                              $envInfo."\n\n".
                              'Alternatively, use http:// instead of https:// for local domains.';

                    throw new PDFNotFoundException(
                        "Could not download PDF from '$url': $errorMessage".$sslHelp
                    );
                }

                // Get HTTP response headers for better debugging
                $headers = @get_headers($url, true);
                $httpCode = $headers ? (isset($headers[0]) ? $headers[0] : 'No response') : 'Could not retrieve headers';

                // Include both original and normalized URLs in error for debugging
                if ($originalUrl !== $url) {
                    throw new PDFNotFoundException(
                        "Could not download PDF from '$originalUrl' (normalized to '$url'): $errorMessage\n".
                        "HTTP Response: $httpCode\n".
                        'Please ensure the URL is accessible and returns a PDF file.'
                    );
                }

                throw new PDFNotFoundException(
                    "Could not download PDF from '$url': $errorMessage\n".
                    "HTTP Response: $httpCode\n".
                    'Please ensure the URL is accessible and returns a PDF file.'
                );
            }

            // Save to temp file
            $result = $this->filesystem->put($filePath, $content);

            if ($result === false) {
                throw new PDFNotFoundException("Could not save downloaded PDF from '$url' to temporary file");
            }

            $this->tmpFiles->push($filePath);

            return $filePath;
        } catch (Exception $e) {
            // Clean up temp file if it was created
            if ($this->filesystem->exists($filePath)) {
                $this->filesystem->delete($filePath);
            }

            if ($e instanceof PDFNotFoundException) {
                throw $e;
            }

            throw new PDFNotFoundException("Could not download PDF from '$url': {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Resolve a path, handling both local filesystem paths and URLs
     *
     * @throws PDFNotFoundException if the file doesn't exist or cannot be downloaded
     */
    protected function resolvePath(string $path): string
    {
        if ($this->isUrl($path)) {
            return $this->downloadUrl($path);
        }

        if (! file_exists($path)) {
            throw new PDFNotFoundException("Could not locate PDF file at '$path'");
        }

        return $path;
    }

    /**
     * Add a PDF for inclusion in the merge with a valid file path or URL. Pages should be formatted: 1,3,6, 12-16.
     *
     * @param  string|array<int>  $pages
     *
     * @throws PDFNotFoundException if the file doesn't exist or URL cannot be downloaded
     * @throws InvalidPagesException if the pages parameter is invalid
     */
    public function addPDF(string $filePath, string|array $pages = 'all', Orientation|string|null $orientation = null): self
    {
        // Resolve the path (handles both local paths and URLs)
        $resolvedPath = $this->resolvePath($filePath);

        if (! is_array($pages) && strtolower($pages) !== 'all') {
            throw new InvalidPagesException("Invalid pages parameter for '$filePath'. Must be 'all' or an array of page numbers.");
        }

        if (is_array($pages)) {
            foreach ($pages as $page) {
                if ($page < 1) {
                    throw new InvalidPagesException("Invalid page number '$page'. Pages must be positive integers.");
                }
            }
        }

        $this->files->push([
            'name' => $resolvedPath,
            'pages' => $pages,
            'orientation' => $orientation,
        ]);

        // Reset merged flag when new files are added
        // This ensures that subsequent merge/output calls will re-merge with all files
        $this->merged = false;

        return $this;
    }

    /**
     * Alias for addPDF
     *
     * @param  string|array<int>  $pages
     */
    public function add(string $filePath, string|array $pages = 'all', Orientation|string|null $orientation = null): self
    {
        return $this->addPDF($filePath, $pages, $orientation);
    }

    /**
     * Alias for addPDF
     *
     * @param  string|array<int>  $pages
     */
    public function addFile(string $filePath, string|array $pages = 'all', Orientation|string|null $orientation = null): self
    {
        return $this->addPDF($filePath, $pages, $orientation);
    }

    /**
     * Shorthand to add a PDF with all pages
     */
    public function addAll(string $filePath, Orientation|string|null $orientation = null): self
    {
        return $this->addPDF($filePath, 'all', $orientation);
    }

    /**
     * Add multiple PDFs at once
     *
     * @param  iterable<mixed>  $files  Array of file configurations, each should contain 'path' key and optional 'pages' and 'orientation' keys
     *
     * @throws InvalidPagesException if a file array is missing the required 'path' key or is not an array
     * @throws PDFNotFoundException if the file doesn't exist
     */
    public function addMany(iterable $files): self
    {
        foreach ($files as $index => $file) {
            if (! is_array($file)) {
                throw new InvalidPagesException("File at index $index must be an array, ".gettype($file).' given.');
            }

            if (! isset($file['path']) && ! array_key_exists('path', $file)) {
                throw new InvalidPagesException("File at index $index is missing the required 'path' key.");
            }

            $this->addPDF(
                $file['path'],
                $file['pages'] ?? 'all',
                $file['orientation'] ?? null
            );
        }

        return $this;
    }

    /**
     * Add a PDF from string content
     *
     * @param  string|array<int>  $pages
     *
     * @throws Exception
     */
    public function addString(string $string, string|array $pages = 'all', Orientation|string|null $orientation = null): self
    {
        $tempPath = config('pdfmerger.temp_path', storage_path('tmp'));

        // Ensure temp directory exists
        if (! $this->filesystem->exists($tempPath)) {
            $this->filesystem->makeDirectory($tempPath, 0755, true);
        }

        $filePath = $tempPath.'/'.Str::random(16).'.pdf';
        $result = $this->filesystem->put($filePath, $string);

        if ($result === false) {
            throw new PDFMergeException('Could not save PDF string content to temporary file');
        }

        $this->tmpFiles->push($filePath);

        return $this->addPDF($filePath, $pages, $orientation);
    }

    /**
     * Set the final filename
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Merges your provided PDFs and outputs to specified location.
     *
     * @throws PDFMergeException if there are no PDFs to merge
     */
    public function merge(Orientation|string|null $orientation = null): self
    {
        if (! $this->merged) {
            // Reset FPDI instance to prevent issues with re-merging
            $this->fpdi = new FPDI;

            $this->doMerge($orientation ?? $this->defaultOrientation, $this->duplexMode);
            $this->merged = true;
        }

        return $this;
    }

    /**
     * Merges your provided PDFs and adds blank pages between documents as needed to allow duplex printing
     *
     * @throws PDFMergeException if there are no PDFs to merge
     */
    public function duplexMerge(Orientation|string|null $orientation = null): self
    {
        if (! $this->merged) {
            // Reset FPDI instance to prevent issues with re-merging
            $this->fpdi = new FPDI;

            $this->doMerge($orientation ?? $this->defaultOrientation, true);
            $this->merged = true;
        }

        return $this;
    }

    /**
     * Internal merge implementation
     *
     * @throws PDFMergeException
     */
    protected function doMerge(Orientation|string|null $orientation, bool $duplexSafe): void
    {
        if ($this->files->count() === 0) {
            throw new PDFMergeException('No PDFs to merge.');
        }

        // Set memory limit from config if available
        $memoryLimit = config('pdfmerger.memory_limit');
        if ($memoryLimit) {
            ini_set('memory_limit', $memoryLimit.'M');
        }

        $fpdi = $this->fpdi;

        $this->files->each(function ($file) use ($fpdi, $orientation, $duplexSafe) {
            $file['orientation'] = $file['orientation'] ?? $orientation;

            // Convert Orientation enum to string if needed
            if ($file['orientation'] instanceof Orientation) {
                $file['orientation'] = $file['orientation']->value;
            }

            try {
                $fileContent = file_get_contents($file['name']);
                if ($fileContent === false) {
                    throw new PDFMergeException("Failed to read PDF file '{$file['name']}'");
                }
                $count = $fpdi->setSourceFile(StreamReader::createByString($fileContent));
            } catch (Exception $e) {
                throw new PDFMergeException("Failed to load PDF '{$file['name']}': {$e->getMessage()}", 0, $e);
            }

            /** @var array{width: float, height: float, orientation: string}|false $size */
            $size = false;

            if ($file['pages'] === 'all') {
                for ($i = 1; $i <= $count; $i++) {
                    $template = $fpdi->importPage($i);
                    $templateSize = $fpdi->getTemplateSize($template);
                    if ($templateSize === false || ! is_array($templateSize)) {
                        throw new PDFMergeException("Failed to get template size for page $i in PDF '{$file['name']}'");
                    }
                    $size = $templateSize;
                    $autoOrientation = $file['orientation'] ?? $size['orientation'];

                    $fpdi->AddPage($autoOrientation, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($template);
                }
            } else {
                /** @var array<int> $pages */
                $pages = $file['pages'];
                foreach ($pages as $page) {
                    $template = $fpdi->importPage($page);
                    if (! $template) {
                        throw new PDFMergeException("Could not load page '$page' in PDF '{$file['name']}'. Check that the page exists.");
                    }
                    $templateSize = $fpdi->getTemplateSize($template);
                    if ($templateSize === false || ! is_array($templateSize)) {
                        throw new PDFMergeException("Failed to get template size for page $page in PDF '{$file['name']}'");
                    }
                    $size = $templateSize;
                    $autoOrientation = $file['orientation'] ?? $size['orientation'];

                    $fpdi->AddPage($autoOrientation, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($template);
                }
            }

            if ($duplexSafe && is_array($size) && ($fpdi->PageNo() % 2) === 1) {
                $fpdi->AddPage($file['orientation'], [$size['width'], $size['height']]);
            }
        });
    }

    /**
     * Get the merged PDF content
     */
    public function output(): string
    {
        if (! $this->merged) {
            $this->merge();
        }

        return $this->fpdi->Output($this->fileName, 'S');
    }

    /**
     * Stream the merged PDF content
     */
    public function stream(): mixed
    {
        if (! $this->merged) {
            $this->merge();
        }

        return $this->fpdi->Output($this->fileName, 'I');
    }

    /**
     * Download the merged PDF content
     */
    public function download(): Response
    {
        $output = $this->output();
        $response = new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->fileName.'"',
            'Content-Length' => strlen($output),
        ]);

        return $response;
    }

    /**
     * Returns a Response object for the merged PDF
     */
    public function toResponse(): Response
    {
        $output = $this->output();

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->fileName.'"',
            'Content-Length' => strlen($output),
        ]);
    }

    /**
     * Returns base64 encoded PDF content
     */
    public function toBase64(): string
    {
        return base64_encode($this->output());
    }

    /**
     * Save the merged PDF content to the filesystem
     */
    public function save(?string $filePath = null): bool
    {
        // Use provided path, or fall back to configured output_path + fileName
        if ($filePath === null) {
            $outputPath = config('pdfmerger.output_path', storage_path('app/pdfs'));

            // Ensure output directory exists
            if (! $this->filesystem->exists($outputPath)) {
                $this->filesystem->makeDirectory($outputPath, 0755, true);
            }

            $filePath = $outputPath.'/'.$this->fileName;
        } else {
            // If explicit path provided, ensure parent directory exists
            $directory = dirname($filePath);
            if (! $this->filesystem->exists($directory)) {
                $this->filesystem->makeDirectory($directory, 0755, true);
            }
        }

        $result = $this->filesystem->put($filePath, $this->output());

        return $result !== false;
    }

    /**
     * Alias for save with clearer naming
     */
    public function saveAs(string $filePath): bool
    {
        return $this->save($filePath);
    }
}
