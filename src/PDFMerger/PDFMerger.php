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
     * @var Collection<int, array{name: string, pages: string|array<int>, orientation: string|null}>
     */
    protected Collection $files;

    /**
     * @var Collection<int, string>
     */
    protected Collection $tmpFiles;

    protected string $fileName = 'undefined.pdf';

    protected ?string $defaultOrientation = null;

    protected bool $duplexMode = false;

    /**
     * Construct and initialize a new instance
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->fpdi = new FPDI;
        $this->files = collect([]);
        $this->tmpFiles = collect([]);
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
        $this->defaultOrientation = null;
        $this->duplexMode = false;

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
    public function orientation(string $orientation): self
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
     * Add a PDF for inclusion in the merge with a valid file path. Pages should be formatted: 1,3,6, 12-16.
     *
     * @param  string|array<int>  $pages
     *
     * @throws PDFNotFoundException if the file doesn't exist
     * @throws InvalidPagesException if the pages parameter is invalid
     */
    public function addPDF(string $filePath, string|array $pages = 'all', ?string $orientation = null): self
    {
        if (! file_exists($filePath)) {
            throw new PDFNotFoundException("Could not locate PDF at '$filePath'");
        }

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
            'name' => $filePath,
            'pages' => $pages,
            'orientation' => $orientation,
        ]);

        return $this;
    }

    /**
     * Alias for addPDF
     *
     * @param  string|array<int>  $pages
     */
    public function add(string $filePath, string|array $pages = 'all', ?string $orientation = null): self
    {
        return $this->addPDF($filePath, $pages, $orientation);
    }

    /**
     * Alias for addPDF
     *
     * @param  string|array<int>  $pages
     */
    public function addFile(string $filePath, string|array $pages = 'all', ?string $orientation = null): self
    {
        return $this->addPDF($filePath, $pages, $orientation);
    }

    /**
     * Shorthand to add a PDF with all pages
     */
    public function addAll(string $filePath, ?string $orientation = null): self
    {
        return $this->addPDF($filePath, 'all', $orientation);
    }

    /**
     * Add multiple PDFs at once
     *
     * @param  iterable<array{path: string, pages?: string|array<int>, orientation?: string|null}>  $files
     */
    public function addMany(iterable $files): self
    {
        foreach ($files as $file) {
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
    public function addString(string $string, string|array $pages = 'all', ?string $orientation = null): self
    {
        $filePath = storage_path('tmp/'.Str::random(16).'.pdf');
        $this->filesystem->put($filePath, $string);
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
    public function merge(?string $orientation = null): self
    {
        $this->doMerge($orientation ?? $this->defaultOrientation, $this->duplexMode);

        return $this;
    }

    /**
     * Merges your provided PDFs and adds blank pages between documents as needed to allow duplex printing
     *
     * @throws PDFMergeException if there are no PDFs to merge
     */
    public function duplexMerge(?string $orientation = 'P'): self
    {
        $this->doMerge($orientation ?? $this->defaultOrientation, true);

        return $this;
    }

    /**
     * Internal merge implementation
     *
     * @throws PDFMergeException
     */
    protected function doMerge(?string $orientation, bool $duplexSafe): void
    {
        if ($this->files->count() === 0) {
            throw new PDFMergeException('No PDFs to merge.');
        }

        $fpdi = $this->fpdi;

        $this->files->each(function ($file) use ($fpdi, $orientation, $duplexSafe) {
            $file['orientation'] = $file['orientation'] ?? $orientation;

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
        return $this->fpdi->Output($this->fileName, 'S');
    }

    /**
     * Stream the merged PDF content
     */
    public function stream(): mixed
    {
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
        $result = $this->filesystem->put($filePath ?? $this->fileName, $this->output());

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
