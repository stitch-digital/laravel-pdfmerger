<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use StitchDigital\PDFMerger\PDFMerger;
use StitchDigital\PDFMerger\Tests\TestCase;

class ConfigIntegrationTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        // Set custom config values for testing
        $app['config']->set('pdfmerger.orientation', 'L');
        $app['config']->set('pdfmerger.duplex', true);
        $app['config']->set('pdfmerger.temp_path', storage_path('custom/tmp'));
        $app['config']->set('pdfmerger.output_path', storage_path('custom/output'));
        $app['config']->set('pdfmerger.memory_limit', 512);
    }

    #[Test]
    public function it_loads_default_orientation_from_config(): void
    {
        $merger = PDFMerger::make();

        // Use reflection to check protected property
        $reflection = new \ReflectionClass($merger);
        $property = $reflection->getProperty('defaultOrientation');
        $property->setAccessible(true);

        $this->assertEquals('L', $property->getValue($merger));
    }

    #[Test]
    public function it_loads_duplex_mode_from_config(): void
    {
        $merger = PDFMerger::make();

        // Use reflection to check protected property
        $reflection = new \ReflectionClass($merger);
        $property = $reflection->getProperty('duplexMode');
        $property->setAccessible(true);

        $this->assertTrue($property->getValue($merger));
    }

    #[Test]
    public function it_can_override_config_defaults(): void
    {
        $merger = PDFMerger::make()
            ->orientation('P')
            ->duplex(false);

        // Use reflection to check protected properties
        $reflection = new \ReflectionClass($merger);

        $orientationProperty = $reflection->getProperty('defaultOrientation');
        $orientationProperty->setAccessible(true);
        $this->assertEquals('P', $orientationProperty->getValue($merger));

        $duplexProperty = $reflection->getProperty('duplexMode');
        $duplexProperty->setAccessible(true);
        $this->assertFalse($duplexProperty->getValue($merger));
    }

    #[Test]
    public function it_resets_to_config_defaults_not_hardcoded_values(): void
    {
        $merger = PDFMerger::make()
            ->orientation('P')
            ->duplex(false)
            ->reset();

        // Use reflection to check protected properties
        $reflection = new \ReflectionClass($merger);

        $orientationProperty = $reflection->getProperty('defaultOrientation');
        $orientationProperty->setAccessible(true);
        $this->assertEquals('L', $orientationProperty->getValue($merger));

        $duplexProperty = $reflection->getProperty('duplexMode');
        $duplexProperty->setAccessible(true);
        $this->assertTrue($duplexProperty->getValue($merger));
    }
}
