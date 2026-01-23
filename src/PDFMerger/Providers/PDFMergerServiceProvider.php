<?php

declare(strict_types=1);

/*
* File:     PDFMergerServiceProvider.php
* Category: Provider
* Author:   M. Goldenbaum
* Created:  01.12.16 20:28
* Updated:  -
*
* Description:
*  -
*/

namespace StitchDigital\PDFMerger\Providers;

use Illuminate\Support\ServiceProvider;
use StitchDigital\PDFMerger\PDFMerger;

class PDFMergerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/pdfmerger.php' => config_path('pdfmerger.php'),
        ], 'pdfmerger-config');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $configPath = __DIR__.'/../../config/pdfmerger.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'pdfmerger');
        }

        $this->app->singleton('PDFMerger', function ($app): PDFMerger {
            return new PDFMerger($app['files']);
        });
    }
}
