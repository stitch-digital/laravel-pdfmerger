<?php

declare(strict_types=1);

/*
* File:     PDFMergerFacade.php
* Category: Facade
* Author:   M. Goldenbaum
* Created:  01.12.16 21:06
* Updated:  -
*
* Description:
*  -
*/

namespace StitchDigital\PDFMerger\Facades;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;
use StitchDigital\PDFMerger\PDFMerger;

/**
 * @method static \StitchDigital\PDFMerger\PDFMerger make()
 * @method static \StitchDigital\PDFMerger\PDFMerger new()
 * @method static \StitchDigital\PDFMerger\PDFMerger init()
 * @method static \StitchDigital\PDFMerger\PDFMerger reset()
 * @method static \StitchDigital\PDFMerger\PDFMerger orientation(string $orientation)
 * @method static \StitchDigital\PDFMerger\PDFMerger duplex(bool $enabled = true)
 * @method static \StitchDigital\PDFMerger\PDFMerger addPDF(string $filePath, string|array<int> $pages = 'all', ?string $orientation = null)
 * @method static \StitchDigital\PDFMerger\PDFMerger add(string $filePath, string|array<int> $pages = 'all', ?string $orientation = null)
 * @method static \StitchDigital\PDFMerger\PDFMerger addFile(string $filePath, string|array<int> $pages = 'all', ?string $orientation = null)
 * @method static \StitchDigital\PDFMerger\PDFMerger addAll(string $filePath, ?string $orientation = null)
 * @method static \StitchDigital\PDFMerger\PDFMerger addMany(iterable<array{path: string, pages?: string|array<int>, orientation?: string|null}> $files)
 * @method static \StitchDigital\PDFMerger\PDFMerger addString(string $string, string|array<int> $pages = 'all', ?string $orientation = null)
 * @method static \StitchDigital\PDFMerger\PDFMerger setFileName(string $fileName)
 * @method static \StitchDigital\PDFMerger\PDFMerger merge(?string $orientation = null)
 * @method static \StitchDigital\PDFMerger\PDFMerger duplexMerge(?string $orientation = 'P')
 * @method static string output()
 * @method static mixed stream()
 * @method static Response download()
 * @method static Response toResponse()
 * @method static string toBase64()
 * @method static bool save(?string $filePath = null)
 * @method static bool saveAs(string $filePath)
 * @method static \StitchDigital\PDFMerger\PDFMerger when($value = null, callable $callback = null, callable $default = null)
 * @method static \StitchDigital\PDFMerger\PDFMerger unless($value = null, callable $callback = null, callable $default = null)
 * @method static \StitchDigital\PDFMerger\PDFMerger tap(callable $callback = null)
 *
 * @see PDFMerger
 */
class PDFMergerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'PDFMerger';
    }
}
