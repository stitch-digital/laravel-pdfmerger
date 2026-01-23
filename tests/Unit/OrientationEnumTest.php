<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests\Unit;

use StitchDigital\PDFMerger\Enums\Orientation;
use StitchDigital\PDFMerger\Tests\TestCase;

class OrientationEnumTest extends TestCase
{
    /** @test */
    public function it_has_portrait_case(): void
    {
        $this->assertEquals('P', Orientation::Portrait->value);
    }

    /** @test */
    public function it_has_landscape_case(): void
    {
        $this->assertEquals('L', Orientation::Landscape->value);
    }

    /** @test */
    public function it_can_be_used_in_switch_statements(): void
    {
        $orientation = Orientation::Portrait;

        $result = match ($orientation) {
            Orientation::Portrait => 'portrait',
            Orientation::Landscape => 'landscape',
        };

        $this->assertEquals('portrait', $result);
    }
}
