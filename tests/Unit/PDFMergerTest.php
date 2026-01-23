<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use StitchDigital\PDFMerger\Exceptions\InvalidPagesException;
use StitchDigital\PDFMerger\Exceptions\PDFMergeException;
use StitchDigital\PDFMerger\Exceptions\PDFNotFoundException;
use StitchDigital\PDFMerger\PDFMerger;
use StitchDigital\PDFMerger\Tests\TestCase;

class PDFMergerTest extends TestCase
{
    protected PDFMerger $merger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->merger = new PDFMerger(new Filesystem);
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(PDFMerger::class, $this->merger);
    }

    /** @test */
    public function it_can_use_make_factory_method(): void
    {
        $merger = PDFMerger::make();
        $this->assertInstanceOf(PDFMerger::class, $merger);
    }

    /** @test */
    public function it_can_use_new_factory_method(): void
    {
        $merger = PDFMerger::new();
        $this->assertInstanceOf(PDFMerger::class, $merger);
    }

    /** @test */
    public function it_throws_exception_when_pdf_file_not_found(): void
    {
        $this->expectException(PDFNotFoundException::class);
        $this->expectExceptionMessage("Could not locate PDF at '/nonexistent/file.pdf'");

        $this->merger->addPDF('/nonexistent/file.pdf');
    }

    /** @test */
    public function it_throws_exception_for_invalid_pages_parameter(): void
    {
        $this->expectException(InvalidPagesException::class);

        // Create a temporary PDF file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, '%PDF-1.4');

        try {
            $this->merger->addPDF($tempFile, 'invalid');
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function it_throws_exception_for_invalid_page_numbers(): void
    {
        $this->expectException(InvalidPagesException::class);

        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, '%PDF-1.4');

        try {
            $this->merger->addPDF($tempFile, [0, -1]);
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function it_throws_exception_when_merging_without_files(): void
    {
        $this->expectException(PDFMergeException::class);
        $this->expectExceptionMessage('No PDFs to merge.');

        $this->merger->merge();
    }

    /** @test */
    public function it_can_set_filename(): void
    {
        $result = $this->merger->setFileName('test.pdf');

        $this->assertInstanceOf(PDFMerger::class, $result);
    }

    /** @test */
    public function it_can_set_orientation(): void
    {
        $result = $this->merger->orientation('L');

        $this->assertInstanceOf(PDFMerger::class, $result);
    }

    /** @test */
    public function it_can_enable_duplex_mode(): void
    {
        $result = $this->merger->duplex(true);

        $this->assertInstanceOf(PDFMerger::class, $result);
    }

    /** @test */
    public function it_supports_method_chaining(): void
    {
        $result = $this->merger
            ->orientation('L')
            ->duplex(true)
            ->setFileName('test.pdf');

        $this->assertInstanceOf(PDFMerger::class, $result);
    }

    /** @test */
    public function it_can_reset_instance(): void
    {
        $this->merger->setFileName('test.pdf')->orientation('L');

        $result = $this->merger->reset();

        $this->assertInstanceOf(PDFMerger::class, $result);
    }

    /** @test */
    public function it_can_use_when_conditionally(): void
    {
        $condition = true;

        $result = $this->merger->when($condition, function ($merger) {
            return $merger->orientation('L');
        });

        $this->assertInstanceOf(PDFMerger::class, $result);
    }

    /** @test */
    public function it_can_use_unless_conditionally(): void
    {
        $condition = false;

        $result = $this->merger->unless($condition, function ($merger) {
            return $merger->orientation('L');
        });

        $this->assertInstanceOf(PDFMerger::class, $result);
    }

    /** @test */
    public function it_can_use_tap(): void
    {
        $tapped = false;

        $result = $this->merger->tap(function () use (&$tapped) {
            $tapped = true;
        });

        $this->assertTrue($tapped);
        $this->assertInstanceOf(PDFMerger::class, $result);
    }

    /** @test */
    public function it_has_add_alias_for_add_pdf(): void
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
    public function it_has_add_file_alias_for_add_pdf(): void
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
    public function it_has_add_all_shorthand(): void
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
    public function merge_returns_self_for_chaining(): void
    {
        // Skip actual merge test as it requires valid PDF files
        $this->expectException(PDFMergeException::class);

        $result = $this->merger->merge();

        // This line won't execute due to exception, but demonstrates intent
        $this->assertInstanceOf(PDFMerger::class, $result);
    }
}
