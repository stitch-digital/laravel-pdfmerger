## Laravel PDFMerger

A modern, fluent PDF merger for Laravel with full type safety and an elegant API. Merge multiple PDF files with ease using a developer-friendly, chainable interface.

### Package Information

- **Namespace**: `StitchDigital\PDFMerger`
- **Facade**: `StitchDigital\PDFMerger\Facades\PDFMergerFacade`
- **Configuration**: `config/pdfmerger.php`
- **Minimum Requirements**: PHP 8.2+, Laravel 10.0+

### Key Features

- **Fluent API**: Chainable methods following Laravel conventions
- **Type Safe**: Full type hints and return types with strict types enabled
- **URL Support**: Add PDFs from both local filesystem paths and remote URLs
- **Enum Support**: Use `Orientation` enum for better IDE support and type safety
- **Page Selection**: Merge specific pages or all pages from PDFs
- **Duplex Mode**: Add blank pages for double-sided printing
- **Multiple Output Methods**: Save, download, stream, or get raw content

### Installation

@verbatim
<code-snippet name="Install via Composer" lang="bash">
composer require stitch-digital/laravel-pdfmerger
</code-snippet>
@endverbatim

Optionally publish the configuration file:

@verbatim
<code-snippet name="Publish configuration" lang="bash">
php artisan vendor:publish --tag=pdfmerger-config
</code-snippet>
@endverbatim

### Basic Usage Pattern

Always use the fluent API with `make()` factory method, chain operations, call `merge()`, and output with `save()`, `download()`, or `stream()`.

@verbatim
<code-snippet name="Basic PDF merge" lang="php">
use StitchDigital\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

PDFMerger::make()
    ->addPDF('/path/to/first.pdf')
    ->addPDF('/path/to/second.pdf')
    ->merge()
    ->save('merged_output.pdf');
</code-snippet>
@endverbatim

### Working with Local Paths

Use Laravel's path helpers (`public_path()`, `storage_path()`, `base_path()`) for local files:

@verbatim
<code-snippet name="Using Laravel path helpers" lang="php">
PDFMerger::make()
    ->addPDF(public_path('pdfs/document1.pdf'))
    ->addPDF(storage_path('app/pdfs/document2.pdf'))
    ->merge()
    ->save(storage_path('app/pdfs/merged.pdf'));
</code-snippet>
@endverbatim

### Working with URLs

The package intelligently detects and downloads PDFs from URLs. Use with `asset()` helper or direct URLs:

@verbatim
<code-snippet name="Using URLs" lang="php">
// With Laravel's asset() helper
PDFMerger::make()
    ->addAll(asset('pdfs/document1.pdf'))
    ->addAll(asset('pdfs/document2.pdf'))
    ->merge()
    ->save('merged.pdf');

// Direct URLs
PDFMerger::make()
    ->addPDF('https://example.com/document.pdf')
    ->addPDF('https://example.com/another.pdf')
    ->merge()
    ->save('merged.pdf');

// Mix local and remote
PDFMerger::make()
    ->addPDF(public_path('pdfs/local.pdf'))
    ->addPDF('https://example.com/remote.pdf')
    ->merge()
    ->save('merged.pdf');
</code-snippet>
@endverbatim

### Method Aliases

Use clear, readable aliases for better code clarity:

@verbatim
<code-snippet name="Method aliases" lang="php">
PDFMerger::make()
    ->add($file)           // Alias for addPDF()
    ->addFile($file)       // Alias for addPDF()
    ->addAll($file)        // Shorthand for addPDF($file, 'all')
    ->merge()
    ->saveAs('output.pdf'); // Alias for save()
</code-snippet>
@endverbatim

### Selecting Specific Pages

Pass an array of page numbers or 'all' as the second parameter:

@verbatim
<code-snippet name="Select specific pages" lang="php">
PDFMerger::make()
    ->addPDF($file, 'all')           // All pages
    ->addPDF($file, [1])             // Only page 1
    ->addPDF($file, [1, 3, 5])       // Pages 1, 3, and 5
    ->merge()
    ->save();
</code-snippet>
@endverbatim

### Setting Orientation

Use the `Orientation` enum (recommended) or strings for page orientation:

@verbatim
<code-snippet name="Set orientation" lang="php">
use StitchDigital\PDFMerger\Enums\Orientation;

// Global orientation
PDFMerger::make()
    ->orientation(Orientation::Landscape)
    ->addPDF($file1)
    ->addPDF($file2)
    ->merge()
    ->save();

// Per-file orientation
PDFMerger::make()
    ->addPDF($file1, pages: 'all', orientation: Orientation::Portrait)
    ->addPDF($file2, pages: 'all', orientation: Orientation::Landscape)
    ->merge()
    ->save();

// String values still supported
PDFMerger::make()
    ->orientation('L')  // 'L' for Landscape, 'P' for Portrait
    ->addPDF($file)
    ->merge()
    ->save();
</code-snippet>
@endverbatim

### Duplex Printing Support

Enable duplex mode to add blank pages for double-sided printing:

@verbatim
<code-snippet name="Enable duplex mode" lang="php">
PDFMerger::make()
    ->duplex(true)
    ->addPDF($file1)
    ->addPDF($file2)
    ->merge()
    ->save();

// Or use duplexMerge() directly
PDFMerger::make()
    ->addPDF($file1)
    ->addPDF($file2)
    ->duplexMerge()
    ->save();
</code-snippet>
@endverbatim

### Output Methods

Multiple ways to output the merged PDF:

@verbatim
<code-snippet name="Output methods" lang="php">
$merger = PDFMerger::make()
    ->addPDF($file1)
    ->addPDF($file2)
    ->merge();

// Save to disk (uses config output_path if no path provided)
$merger->save('output.pdf');
$merger->saveAs('output.pdf');  // Alias

// Download in browser
return $merger->download();

// Stream to browser
return $merger->stream();

// Return as Response object
return $merger->toResponse();

// Get raw PDF content
$content = $merger->output();

// Get as base64 encoded string
$base64 = $merger->toBase64();
</code-snippet>
@endverbatim

### Adding Multiple Files at Once

Use `addMany()` with an array of file configurations:

@verbatim
<code-snippet name="Add many files" lang="php">
$files = [
    ['path' => '/path/to/file1.pdf', 'pages' => [1, 2]],
    ['path' => '/path/to/file2.pdf', 'pages' => 'all'],
    ['path' => 'https://example.com/file3.pdf', 'pages' => [1], 'orientation' => Orientation::Landscape],
];

PDFMerger::make()
    ->addMany($files)
    ->merge()
    ->save();
</code-snippet>
@endverbatim

### Working with String Content

Add PDFs from string content (useful for API responses or generated PDFs):

@verbatim
<code-snippet name="Add from string" lang="php">
$pdfContent = file_get_contents('/path/to/file.pdf');

PDFMerger::make()
    ->addString($pdfContent, pages: [1, 2])
    ->merge()
    ->save();
</code-snippet>
@endverbatim

### Configuration

Configure defaults in `config/pdfmerger.php`:

- **temp_path**: Directory for temporary files (default: `storage_path('tmp/pdfmerger')`)
- **output_path**: Default output directory (default: `storage_path('app/pdfs')`)
- **orientation**: Default page orientation (default: `'P'` for Portrait)
- **duplex**: Enable duplex mode by default (default: `false`)
- **memory_limit**: Memory limit in MB (default: `256`)
- **allow_urls**: Enable/disable URL downloads (default: `true`)
- **url_download_timeout**: Timeout for URL downloads in seconds (default: `30`)
- **url_verify_ssl**: Verify SSL certificates for HTTPS (default: `true`)
- **disk**: Default Storage disk (default: `'local'`)

### Error Handling

The package throws specific exceptions for different scenarios:

@verbatim
<code-snippet name="Handle exceptions" lang="php">
use StitchDigital\PDFMerger\Exceptions\PDFNotFoundException;
use StitchDigital\PDFMerger\Exceptions\InvalidPagesException;
use StitchDigital\PDFMerger\Exceptions\PDFMergeException;

try {
    PDFMerger::make()
        ->addPDF('/path/to/file.pdf')
        ->merge()
        ->save();
} catch (PDFNotFoundException $e) {
    // File not found or URL download failed
    // Examples:
    // - "Could not locate PDF file at '/path/to/file.pdf'"
    // - "Could not download PDF from 'https://example.com/file.pdf': Connection timeout"
    // - "URL downloads are disabled in configuration"
} catch (InvalidPagesException $e) {
    // Invalid pages parameter or page numbers
} catch (PDFMergeException $e) {
    // General merge error (e.g., no PDFs added, corrupted PDF)
}
</code-snippet>
@endverbatim

### Conditional Operations

Use `when()` and `unless()` for conditional logic:

@verbatim
<code-snippet name="Conditional operations" lang="php">
$merger = PDFMerger::make()
    ->when($includeFirstFile, fn($m) => $m->addPDF($file1))
    ->unless($skipSecondFile, fn($m) => $m->addPDF($file2))
    ->merge();
</code-snippet>
@endverbatim

### Resetting and Reusing

Reset the merger instance to reuse it:

@verbatim
<code-snippet name="Reset and reuse" lang="php">
$merger = PDFMerger::make()
    ->addPDF($file1)
    ->merge()
    ->save('first.pdf');

// Reset and reuse
$merger->reset()
    ->addPDF($file2)
    ->merge()
    ->save('second.pdf');
</code-snippet>
@endverbatim

### Extending with Macros

Extend the package with custom methods:

@verbatim
<code-snippet name="Add custom macro" lang="php">
use StitchDigital\PDFMerger\PDFMerger;

// In a service provider
PDFMerger::macro('addDirectory', function ($directory) {
    foreach (glob($directory . '/*.pdf') as $file) {
        $this->addPDF($file);
    }
    return $this;
});

// Usage
PDFMerger::make()
    ->addDirectory('/path/to/pdfs')
    ->merge()
    ->save();
</code-snippet>
@endverbatim

### Security Best Practices

- For production environments, consider disabling URL support if not needed: `'allow_urls' => false`
- Keep SSL verification enabled for HTTPS URLs: `'url_verify_ssl' => true`
- Only download PDFs from trusted sources when using URLs
- Use Laravel's path helpers instead of user input for file paths
- Validate and sanitize any user-provided file paths or URLs
- Set appropriate memory limits for large PDF operations

### Common Patterns

#### Route with Download Response
@verbatim
<code-snippet name="Download route" lang="php">
Route::get('/merge-pdfs', function () {
    return PDFMerger::make()
        ->addPDF(storage_path('app/pdfs/doc1.pdf'))
        ->addPDF(storage_path('app/pdfs/doc2.pdf'))
        ->merge()
        ->download();
});
</code-snippet>
@endverbatim

#### Controller Method
@verbatim
<code-snippet name="Controller usage" lang="php">
public function mergePdfs(Request $request)
{
    $files = $request->validate([
        'files' => 'required|array|min:2',
        'files.*' => 'required|file|mimes:pdf|max:10240',
    ]);

    $merger = PDFMerger::make();

    foreach ($files['files'] as $file) {
        $path = $file->store('temp-pdfs');
        $merger->addPDF(storage_path('app/' . $path));
    }

    $output = $merger
        ->merge()
        ->setFileName('merged-documents.pdf')
        ->output();

    // Clean up temporary files...

    return response($output, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="merged-documents.pdf"',
    ]);
}
</code-snippet>
@endverbatim

### Testing

When testing, create temporary PDF files or use the package's example PDFs:

@verbatim
<code-snippet name="Test example" lang="php">
public function test_can_merge_pdfs()
{
    $tempFile1 = tempnam(sys_get_temp_dir(), 'pdf');
    $tempFile2 = tempnam(sys_get_temp_dir(), 'pdf');
    file_put_contents($tempFile1, '%PDF-1.4');
    file_put_contents($tempFile2, '%PDF-1.4');

    $result = PDFMerger::make()
        ->addPDF($tempFile1)
        ->addPDF($tempFile2)
        ->merge();

    $this->assertInstanceOf(PDFMerger::class, $result);

    unlink($tempFile1);
    unlink($tempFile2);
}
</code-snippet>
@endverbatim
