<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use StitchDigital\PDFMerger\Facades\PDFMergerFacade;
use StitchDigital\PDFMerger\Providers\PDFMergerServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PDFMergerServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'PDFMerger' => PDFMergerFacade::class,
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
