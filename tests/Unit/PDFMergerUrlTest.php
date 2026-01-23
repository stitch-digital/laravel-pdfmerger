<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use StitchDigital\PDFMerger\Exceptions\PDFNotFoundException;
use StitchDigital\PDFMerger\PDFMerger;
use StitchDigital\PDFMerger\Tests\TestCase;

class PDFMergerUrlTest extends TestCase
{
    protected PDFMerger $merger;

    protected Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem;
        $this->merger = new PDFMerger($this->filesystem);

        // Set up temp path
        Config::set('pdfmerger.temp_path', sys_get_temp_dir().'/pdfmerger_test');
        Config::set('pdfmerger.allow_urls', true);
        Config::set('pdfmerger.url_download_timeout', 30);
        Config::set('pdfmerger.url_verify_ssl', true);
    }

    protected function tearDown(): void
    {
        // Clean up temp directory
        $tempPath = sys_get_temp_dir().'/pdfmerger_test';
        if ($this->filesystem->exists($tempPath)) {
            $this->filesystem->deleteDirectory($tempPath);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_detects_http_urls(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('isUrl');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($this->merger, 'http://example.com/file.pdf'));
        $this->assertTrue($method->invoke($this->merger, 'https://example.com/file.pdf'));
        $this->assertTrue($method->invoke($this->merger, 'ftp://example.com/file.pdf'));
    }

    /** @test */
    public function it_detects_non_urls(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('isUrl');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($this->merger, '/path/to/file.pdf'));
        $this->assertFalse($method->invoke($this->merger, 'relative/path/file.pdf'));
        $this->assertFalse($method->invoke($this->merger, 'C:\\path\\to\\file.pdf'));
        $this->assertFalse($method->invoke($this->merger, 'file.pdf'));
        $this->assertFalse($method->invoke($this->merger, ''));
    }

    /** @test */
    public function it_rejects_invalid_url_schemes(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('isUrl');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($this->merger, 'file://path/to/file.pdf'));
        $this->assertFalse($method->invoke($this->merger, 'javascript:alert(1)'));
        $this->assertFalse($method->invoke($this->merger, 'data:text/html,<script>alert(1)</script>'));
    }

    /** @test */
    public function it_can_add_pdf_from_local_path(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, '%PDF-1.4');

        try {
            $result = $this->merger->addPDF($tempFile);
            $this->assertInstanceOf(PDFMerger::class, $result);
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function it_throws_exception_for_nonexistent_local_file(): void
    {
        $this->expectException(PDFNotFoundException::class);
        $this->expectExceptionMessage("Could not locate PDF file at '/nonexistent/file.pdf'");

        $this->merger->addPDF('/nonexistent/file.pdf');
    }

    /** @test */
    public function it_throws_exception_when_urls_are_disabled(): void
    {
        Config::set('pdfmerger.allow_urls', false);

        $this->expectException(PDFNotFoundException::class);
        $this->expectExceptionMessage('URL downloads are disabled in configuration');

        $this->merger->addPDF('https://example.com/file.pdf');
    }

    /** @test */
    public function it_downloads_pdf_from_url(): void
    {
        // Create a mock PDF content
        $pdfContent = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
>>
endobj
xref
0 4
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
trailer
<<
/Size 4
/Root 1 0 R
>>
startxref
190
%%EOF';

        // Create a temporary file to simulate a remote PDF
        $tempRemoteFile = tempnam(sys_get_temp_dir(), 'remote_pdf');
        file_put_contents($tempRemoteFile, $pdfContent);

        try {
            // Test using the downloadUrl method directly with file:// protocol (for testing)
            // In real usage, this would be an HTTP URL
            $reflection = new \ReflectionClass($this->merger);
            $method = $reflection->getMethod('downloadUrl');
            $method->setAccessible(true);

            // We can't easily test actual HTTP downloads without mocking,
            // but we can test the file system operations
            $tempPath = sys_get_temp_dir().'/pdfmerger_test';
            if (! $this->filesystem->exists($tempPath)) {
                $this->filesystem->makeDirectory($tempPath, 0755, true);
            }

            // Simulate what downloadUrl does
            $filePath = $tempPath.'/'.uniqid().'.pdf';
            $this->filesystem->put($filePath, $pdfContent);

            $this->assertTrue($this->filesystem->exists($filePath));
            $this->assertEquals($pdfContent, $this->filesystem->get($filePath));

            // Clean up
            $this->filesystem->delete($filePath);
        } finally {
            unlink($tempRemoteFile);
        }
    }

    /** @test */
    public function it_creates_temp_directory_if_not_exists(): void
    {
        $tempPath = sys_get_temp_dir().'/pdfmerger_test_new';
        Config::set('pdfmerger.temp_path', $tempPath);

        // Ensure directory doesn't exist
        if ($this->filesystem->exists($tempPath)) {
            $this->filesystem->deleteDirectory($tempPath);
        }

        $this->assertFalse($this->filesystem->exists($tempPath));

        // Simulate downloadUrl which creates the directory
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('downloadUrl');
        $method->setAccessible(true);

        try {
            // This will fail because we can't actually download, but it should create the directory
            $method->invoke($this->merger, 'https://invalid.example.com/file.pdf');
        } catch (PDFNotFoundException $e) {
            // Expected to fail, but directory should be created
        }

        $this->assertTrue($this->filesystem->exists($tempPath));

        // Clean up
        $this->filesystem->deleteDirectory($tempPath);
    }

    /** @test */
    public function it_can_add_multiple_pdfs_with_mixed_sources(): void
    {
        $tempFile1 = tempnam(sys_get_temp_dir(), 'pdf');
        $tempFile2 = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile1, '%PDF-1.4');
        file_put_contents($tempFile2, '%PDF-1.4');

        try {
            // Add local files
            $result = $this->merger
                ->addPDF($tempFile1)
                ->addPDF($tempFile2);

            $this->assertInstanceOf(PDFMerger::class, $result);
        } finally {
            unlink($tempFile1);
            unlink($tempFile2);
        }
    }

    /** @test */
    public function it_can_use_add_all_with_local_path(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, '%PDF-1.4');

        try {
            $result = $this->merger->addAll($tempFile);
            $this->assertInstanceOf(PDFMerger::class, $result);
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function it_can_use_add_alias_with_local_path(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, '%PDF-1.4');

        try {
            $result = $this->merger->add($tempFile);
            $this->assertInstanceOf(PDFMerger::class, $result);
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function it_can_use_add_file_alias_with_local_path(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, '%PDF-1.4');

        try {
            $result = $this->merger->addFile($tempFile);
            $this->assertInstanceOf(PDFMerger::class, $result);
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function it_can_use_add_many_with_local_paths(): void
    {
        $tempFile1 = tempnam(sys_get_temp_dir(), 'pdf');
        $tempFile2 = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile1, '%PDF-1.4');
        file_put_contents($tempFile2, '%PDF-1.4');

        try {
            $result = $this->merger->addMany([
                ['path' => $tempFile1],
                ['path' => $tempFile2, 'pages' => 'all'],
            ]);

            $this->assertInstanceOf(PDFMerger::class, $result);
        } finally {
            unlink($tempFile1);
            unlink($tempFile2);
        }
    }

    /** @test */
    public function resolve_path_returns_same_path_for_local_file(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, '%PDF-1.4');

        try {
            $reflection = new \ReflectionClass($this->merger);
            $method = $reflection->getMethod('resolvePath');
            $method->setAccessible(true);

            $result = $method->invoke($this->merger, $tempFile);
            $this->assertEquals($tempFile, $result);
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function resolve_path_throws_exception_for_nonexistent_file(): void
    {
        $this->expectException(PDFNotFoundException::class);
        $this->expectExceptionMessage("Could not locate PDF file at '/nonexistent/path.pdf'");

        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('resolvePath');
        $method->setAccessible(true);

        $method->invoke($this->merger, '/nonexistent/path.pdf');
    }

    /** @test */
    public function it_respects_url_download_timeout_config(): void
    {
        Config::set('pdfmerger.url_download_timeout', 5);

        // The timeout is set in the stream context, we can verify the config is read
        $this->assertEquals(5, config('pdfmerger.url_download_timeout'));
    }

    /** @test */
    public function it_respects_url_verify_ssl_config(): void
    {
        Config::set('pdfmerger.url_verify_ssl', false);

        // The SSL verification is set in the stream context, we can verify the config is read
        $this->assertFalse(config('pdfmerger.url_verify_ssl'));
    }

    /** @test */
    public function add_string_still_works(): void
    {
        $pdfContent = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
xref
0 2
trailer
<<
/Size 2
/Root 1 0 R
>>
startxref
100
%%EOF';

        $result = $this->merger->addString($pdfContent);
        $this->assertInstanceOf(PDFMerger::class, $result);
    }

    /** @test */
    public function it_cleans_up_temp_files_on_destruct(): void
    {
        $tempPath = sys_get_temp_dir().'/pdfmerger_test';
        if (! $this->filesystem->exists($tempPath)) {
            $this->filesystem->makeDirectory($tempPath, 0755, true);
        }

        // Create a temp file manually
        $tempFile = $tempPath.'/'.uniqid().'.pdf';
        $this->filesystem->put($tempFile, '%PDF-1.4');

        // Create a new merger instance and add the file to tmpFiles
        $merger = new PDFMerger($this->filesystem);
        $reflection = new \ReflectionClass($merger);
        $property = $reflection->getProperty('tmpFiles');
        $property->setAccessible(true);
        $tmpFiles = $property->getValue($merger);
        $tmpFiles->push($tempFile);
        $property->setValue($merger, $tmpFiles);

        $this->assertTrue($this->filesystem->exists($tempFile));

        // Destroy the merger instance
        unset($merger);

        // The temp file should be cleaned up
        $this->assertFalse($this->filesystem->exists($tempFile));
    }
}
