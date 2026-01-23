<?php

/**
 * This file demonstrates how to use the Orientation enum
 * for better developer experience and type safety
 */

use StitchDigital\PDFMerger\Enums\Orientation;
use StitchDigital\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

// Example 1: Using Orientation enum for default orientation
PDFMerger::make()
    ->orientation(Orientation::Landscape)
    ->addPDF('/path/to/file1.pdf')
    ->addPDF('/path/to/file2.pdf')
    ->merge()
    ->save('output.pdf');

// Example 2: Per-file orientation with enum
PDFMerger::make()
    ->addPDF('/path/to/portrait.pdf', pages: 'all', orientation: Orientation::Portrait)
    ->addPDF('/path/to/landscape.pdf', pages: 'all', orientation: Orientation::Landscape)
    ->merge()
    ->save('mixed-orientation.pdf');

// Example 3: Using enum with named parameters
PDFMerger::make()
    ->addPDF(
        filePath: '/path/to/file.pdf',
        pages: [1, 2, 3],
        orientation: Orientation::Portrait
    )
    ->merge()
    ->save('output.pdf');

// Example 4: Conditional orientation with enum
$isLandscape = true;

PDFMerger::make()
    ->when($isLandscape, fn ($merger) => $merger->orientation(Orientation::Landscape))
    ->unless($isLandscape, fn ($merger) => $merger->orientation(Orientation::Portrait))
    ->addPDF('/path/to/file.pdf')
    ->merge()
    ->save('output.pdf');

// Example 5: Backward compatible - strings still work
PDFMerger::make()
    ->orientation('L')  // 'L' for Landscape, 'P' for Portrait
    ->addPDF('/path/to/file.pdf')
    ->merge()
    ->save('output.pdf');

// Example 6: Using enum values in match expressions
$orientation = Orientation::Landscape;

$description = match ($orientation) {
    Orientation::Portrait => 'This will be in portrait mode',
    Orientation::Landscape => 'This will be in landscape mode',
};

// Example 7: IDE autocomplete benefits
// When you type Orientation:: your IDE will suggest:
// - Orientation::Portrait
// - Orientation::Landscape
