<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use StitchDigital\PDFMerger\Providers\PDFMergerServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        // Skip parent setUp to avoid Orchestra Testbench latestResponse bug in older versions
        // This is safe because we're only testing a simple package without HTTP features
        if (method_exists(parent::class, 'setUpTheTestEnvironment')) {
            // Orchestra Testbench 8.23+ has this method
            parent::setUp();
        } else {
            // For older versions, call the grandparent directly
            \PHPUnit\Framework\TestCase::setUp();
            $this->setUpTheTestEnvironment();
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            PDFMergerServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'PDFMerger' => \StitchDigital\PDFMerger\Facades\PDFMergerFacade::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default environment configuration
    }

    /**
     * Get the path to a test fixture file
     */
    protected function getFixturePath(string $filename): string
    {
        return __DIR__.'/Fixtures/'.$filename;
    }
}
