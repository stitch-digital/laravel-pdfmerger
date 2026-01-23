<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests\Feature;

use StitchDigital\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use StitchDigital\PDFMerger\Tests\TestCase;

class PDFMergeFacadeTest extends TestCase
{
    /** @test */
    public function it_can_access_facade(): void
    {
        $merger = PDFMerger::make();

        $this->assertInstanceOf(\StitchDigital\PDFMerger\PDFMerger::class, $merger);
    }

    /** @test */
    public function it_supports_fluent_api_through_facade(): void
    {
        $merger = PDFMerger::make()
            ->orientation('L')
            ->duplex(true)
            ->setFileName('test.pdf');

        $this->assertInstanceOf(\StitchDigital\PDFMerger\PDFMerger::class, $merger);
    }

    /** @test */
    public function facade_supports_conditional_methods(): void
    {
        $merger = PDFMerger::make()
            ->when(true, fn ($m) => $m->orientation('L'))
            ->unless(false, fn ($m) => $m->duplex(true));

        $this->assertInstanceOf(\StitchDigital\PDFMerger\PDFMerger::class, $merger);
    }
}
