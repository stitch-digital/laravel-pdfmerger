<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Enums;

enum Orientation: string
{
    case Portrait = 'P';
    case Landscape = 'L';
}
