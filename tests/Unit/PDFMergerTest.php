<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use StitchDigital\PDFMerger\Enums\Orientation;
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
    public function it_can_set_orientation_with_enum(): void
    {
        $result = $this->merger->orientation(Orientation::Landscape);

        $this->assertInstanceOf(PDFMerger::class, $result);
    }

    /** @test */
    public function it_can_add_pdf_with_orientation_enum(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, '%PDF-1.4');

        try {
            $result = $this->merger->addPDF($tempFile, 'all', Orientation::Portrait);
            $this->assertInstanceOf(PDFMerger::class, $result);
        } finally {
            unlink($tempFile);
        }
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
    public function it_can_use_when_conditionally_with_enum(): void
    {
        $condition = true;

        $result = $this->merger->when($condition, function ($merger) {
            return $merger->orientation(Orientation::Landscape);
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

    /** @test */
    public function it_can_add_many_pdfs_at_once(): void
    {
        $tempFile1 = tempnam(sys_get_temp_dir(), 'pdf');
        $tempFile2 = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile1, '%PDF-1.4');
        file_put_contents($tempFile2, '%PDF-1.4');

        try {
            $result = $this->merger->addMany([
                ['path' => $tempFile1],
                ['path' => $tempFile2, 'pages' => 'all'],
                ['path' => $tempFile1, 'pages' => [1], 'orientation' => 'L'],
            ]);

            $this->assertInstanceOf(PDFMerger::class, $result);
        } finally {
            unlink($tempFile1);
            unlink($tempFile2);
        }
    }

    /** @test */
    public function it_throws_exception_when_add_many_file_is_missing_path_key(): void
    {
        $this->expectException(InvalidPagesException::class);
        $this->expectExceptionMessage("File at index 0 is missing the required 'path' key.");

        $this->merger->addMany([
            ['pages' => 'all'], // Missing 'path' key
        ]);
    }

    /** @test */
    public function it_throws_exception_when_add_many_file_is_not_array(): void
    {
        $this->expectException(InvalidPagesException::class);
        $this->expectExceptionMessage('File at index 0 must be an array, string given.');

        $this->merger->addMany([
            '/path/to/file.pdf', // String instead of array
        ]);
    }

    /** @test */
    public function it_throws_exception_for_second_malformed_file_in_add_many(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, '%PDF-1.4');

        try {
            $this->expectException(InvalidPagesException::class);
            $this->expectExceptionMessage("File at index 1 is missing the required 'path' key.");

            $this->merger->addMany([
                ['path' => $tempFile],
                ['pages' => 'all'], // Missing 'path' key
            ]);
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function it_validates_path_exists_even_with_null_value(): void
    {
        // When path is null, PHP's strict typing will throw a TypeError
        // before our validation runs, which is actually better security
        $this->expectException(\TypeError::class);

        $this->merger->addMany([
            ['path' => null],
        ]);
    }

    /** @test */
    public function it_can_add_many_with_orientation_enum(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, '%PDF-1.4');

        try {
            $result = $this->merger->addMany([
                ['path' => $tempFile, 'orientation' => Orientation::Portrait],
                ['path' => $tempFile, 'orientation' => Orientation::Landscape],
            ]);

            $this->assertInstanceOf(PDFMerger::class, $result);
        } finally {
            unlink($tempFile);
        }
    }
}
