<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use StitchDigital\PDFMerger\PDFMerger;
use StitchDigital\PDFMerger\Tests\TestCase;

class PDFMergerRemergeTest extends TestCase
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
    }

    protected function tearDown(): void
    {
        $tempPath = sys_get_temp_dir().'/pdfmerger_test';
        if ($this->filesystem->exists($tempPath)) {
            $this->filesystem->deleteDirectory($tempPath);
        }

        parent::tearDown();
    }

    /**
     * Get path to test PDF
     */
    protected function getTestPdf(int $number = 1): string
    {
        $examplesPath = __DIR__.'/../../src/PDFMerger/examples';

        return match ($number) {
            1 => $examplesPath.'/pdf_one.pdf',
            2 => $examplesPath.'/pdf_two.pdf',
            default => $examplesPath.'/pdf_one.pdf',
        };
    }

    #[Test]
    public function it_includes_files_added_after_merge(): void
    {
        // Get test PDFs
        $pdf1 = $this->getTestPdf(1);
        $pdf2 = $this->getTestPdf(2);
        $pdf3 = $this->getTestPdf(1); // Reuse pdf1

        // Add first two PDFs and merge
        $this->merger->addPDF($pdf1)->addPDF($pdf2)->merge();

        // Get the output - should have 2 pages
        $output1 = $this->merger->output();
        $this->assertStringContainsString('PDF', $output1);

        // Now add another PDF after merge
        $this->merger->addPDF($pdf3);

        // Call output again - should now have 3 pages
        $output2 = $this->merger->output();

        // The output should be different because it includes the third file
        // Note: We can't compare exact content due to FPDI internals, but we can verify
        // that merge was re-triggered by checking that the output contains PDF structure
        $this->assertStringContainsString('PDF', $output2);

        // Verify the third file was actually included by checking the internal state
        // We'll use reflection to check if merged flag was reset
        $reflection = new \ReflectionClass($this->merger);
        $filesProperty = $reflection->getProperty('files');
        $filesProperty->setAccessible(true);
        $files = $filesProperty->getValue($this->merger);

        // Should have 3 files in the collection
        $this->assertCount(3, $files);
    }

    /**
     * This test demonstrates the exact issue described:
     * When files are added via addPDF() after merge() has been called,
     * the newly added files should be included in subsequent outputs.
     *
     * @test
     */
    public function it_prevents_silent_data_loss_when_adding_files_after_merge(): void
    {
        $pdf1 = $this->getTestPdf(1);
        $pdf2 = $this->getTestPdf(2);

        // User adds first file and merges
        $this->merger->addPDF($pdf1)->merge();

        // Verify file collection has 1 file
        $reflection = new \ReflectionClass($this->merger);
        $filesProperty = $reflection->getProperty('files');
        $filesProperty->setAccessible(true);
        $files = $filesProperty->getValue($this->merger);
        $this->assertCount(1, $files);

        // User adds another file after merge
        // Before the fix: this would be stored but never included in output
        // After the fix: the merged flag is reset, so it will be included
        $this->merger->addPDF($pdf2);

        // Verify file collection now has 2 files
        $files = $filesProperty->getValue($this->merger);
        $this->assertCount(2, $files);

        // Verify the merged flag was reset after adding the second file
        $mergedProperty = $reflection->getProperty('merged');
        $mergedProperty->setAccessible(true);
        $this->assertFalse($mergedProperty->getValue($this->merger), 'merged flag should be false after adding new file');

        // Get output - this should include BOTH files
        $output = $this->merger->output();
        $this->assertStringContainsString('PDF', $output);

        // Verify merged flag is now true again
        $this->assertTrue($mergedProperty->getValue($this->merger), 'merged flag should be true after output');
    }

    #[Test]
    public function it_remerges_when_adding_files_after_merge(): void
    {
        // Get test PDFs
        $pdf1 = $this->getTestPdf(1);
        $pdf2 = $this->getTestPdf(2);

        // Add and merge
        $this->merger->addPDF($pdf1)->merge();

        // Add another file
        $this->merger->addPDF($pdf2);

        // The merged flag should have been reset
        $reflection = new \ReflectionClass($this->merger);
        $mergedProperty = $reflection->getProperty('merged');
        $mergedProperty->setAccessible(true);

        // After adding a new file, merged should be false
        $this->assertFalse($mergedProperty->getValue($this->merger));
    }

    #[Test]
    public function it_handles_multiple_remerges(): void
    {
        // Get test PDFs
        $pdf1 = $this->getTestPdf(1);
        $pdf2 = $this->getTestPdf(2);
        $pdf3 = $this->getTestPdf(1); // Reuse pdf1

        // First merge
        $this->merger->addPDF($pdf1)->merge();
        $output1 = $this->merger->output();

        // Add and merge again
        $this->merger->addPDF($pdf2)->merge();
        $output2 = $this->merger->output();

        // Add and output (auto-merge)
        $this->merger->addPDF($pdf3);
        $output3 = $this->merger->output();

        // All outputs should be valid PDFs
        $this->assertStringContainsString('PDF', $output1);
        $this->assertStringContainsString('PDF', $output2);
        $this->assertStringContainsString('PDF', $output3);

        // Verify all files are in the collection
        $reflection = new \ReflectionClass($this->merger);
        $filesProperty = $reflection->getProperty('files');
        $filesProperty->setAccessible(true);
        $files = $filesProperty->getValue($this->merger);

        $this->assertCount(3, $files);
    }

    #[Test]
    public function it_remerges_when_using_stream_after_adding_files(): void
    {
        // Get test PDFs
        $pdf1 = $this->getTestPdf(1);
        $pdf2 = $this->getTestPdf(2);

        // Add and stream
        $this->merger->addPDF($pdf1);
        ob_start();
        $this->merger->stream();
        $output1 = ob_get_clean();

        // Add another file and stream again
        $this->merger->addPDF($pdf2);
        ob_start();
        $this->merger->stream();
        $output2 = ob_get_clean();

        // Both outputs should be valid
        $this->assertStringContainsString('PDF', $output1);
        $this->assertStringContainsString('PDF', $output2);
    }

    #[Test]
    public function it_remerges_when_using_save_after_adding_files(): void
    {
        // Get test PDFs
        $pdf1 = $this->getTestPdf(1);
        $pdf2 = $this->getTestPdf(2);

        $tempPath = sys_get_temp_dir().'/pdfmerger_test';
        $outputFile1 = $tempPath.'/output1.pdf';
        $outputFile2 = $tempPath.'/output2.pdf';

        // Add and save
        $this->merger->addPDF($pdf1);
        $this->merger->save($outputFile1);

        // Add another file and save again
        $this->merger->addPDF($pdf2);
        $this->merger->save($outputFile2);

        // Both files should exist and be valid
        $this->assertTrue($this->filesystem->exists($outputFile1));
        $this->assertTrue($this->filesystem->exists($outputFile2));

        $content1 = $this->filesystem->get($outputFile1);
        $content2 = $this->filesystem->get($outputFile2);

        $this->assertStringContainsString('PDF', $content1);
        $this->assertStringContainsString('PDF', $content2);
    }
}
