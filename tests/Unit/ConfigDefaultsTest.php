<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use StitchDigital\PDFMerger\PDFMerger;
use StitchDigital\PDFMerger\Tests\TestCase;

class ConfigDefaultsTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        // Don't set any custom config - test the package defaults
        // Config will return defaults from config/pdfmerger.php
    }

    #[Test]
    public function it_uses_portrait_as_default_orientation_when_config_not_customized(): void
    {
        $merger = PDFMerger::make();

        // Use reflection to check protected property
        $reflection = new \ReflectionClass($merger);
        $property = $reflection->getProperty('defaultOrientation');
        $property->setAccessible(true);

        // Default from config/pdfmerger.php is 'P'
        $this->assertEquals('P', $property->getValue($merger));
    }

    #[Test]
    public function it_uses_false_as_default_duplex_when_config_not_customized(): void
    {
        $merger = PDFMerger::make();

        // Use reflection to check protected property
        $reflection = new \ReflectionClass($merger);
        $property = $reflection->getProperty('duplexMode');
        $property->setAccessible(true);

        // Default from config/pdfmerger.php is false
        $this->assertFalse($property->getValue($merger));
    }
}
