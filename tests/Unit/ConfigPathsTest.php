<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests\Unit;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use StitchDigital\PDFMerger\PDFMerger;
use StitchDigital\PDFMerger\Tests\TestCase;

class ConfigPathsTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        // Set custom paths for testing
        $app['config']->set('pdfmerger.temp_path', storage_path('test/tmp'));
        $app['config']->set('pdfmerger.output_path', storage_path('test/output'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Clean up test directories
        File::deleteDirectory(storage_path('test'));
    }

    protected function tearDown(): void
    {
        // Clean up test directories
        File::deleteDirectory(storage_path('test'));

        parent::tearDown();
    }

    #[Test]
    public function it_creates_temp_files_in_configured_path(): void
    {
        $merger = PDFMerger::make();

        // Use a real PDF from examples
        $pdfPath = __DIR__.'/../../src/PDFMerger/examples/pdf_one.pdf';
        $pdfContent = File::get($pdfPath);

        // Add PDF from string (this should use configured temp_path)
        $merger->addString($pdfContent);

        // Verify temp directory was created
        $this->assertTrue(File::exists(storage_path('test/tmp')));

        // Verify at least one file was created in temp directory
        $files = File::files(storage_path('test/tmp'));
        $this->assertCount(1, $files);
    }

    #[Test]
    public function it_saves_files_to_configured_output_path_when_no_path_specified(): void
    {
        $merger = PDFMerger::make();

        // Use a real PDF from examples
        $pdfPath = __DIR__.'/../../src/PDFMerger/examples/pdf_one.pdf';

        $merger->addPDF($pdfPath)
            ->setFileName('merged.pdf')
            ->merge()
            ->save(); // No path specified, should use configured output_path

        // Verify output directory was created
        $this->assertTrue(File::exists(storage_path('test/output')));

        // Verify file was saved in configured output path
        $this->assertTrue(File::exists(storage_path('test/output/merged.pdf')));
    }

    #[Test]
    public function it_respects_explicit_path_over_config(): void
    {
        $merger = PDFMerger::make();

        // Use a real PDF from examples
        $pdfPath = __DIR__.'/../../src/PDFMerger/examples/pdf_one.pdf';
        $explicitPath = storage_path('explicit/merged.pdf');

        $merger->addPDF($pdfPath)
            ->merge()
            ->save($explicitPath); // Explicit path should be used

        // Verify file was saved at explicit path, not config path
        $this->assertTrue(File::exists($explicitPath));
        $this->assertFalse(File::exists(storage_path('test/output/undefined.pdf')));

        // Clean up
        File::deleteDirectory(storage_path('explicit'));
    }
}
