# Orientation Enum Implementation

## Overview
This update introduces a PHP 8.2+ backed enum for orientation parameters, providing better developer experience through IDE autocomplete, type safety, and clearer intent.

## Changes Made

### 1. New Enum Class
**File**: `src/PDFMerger/Enums/Orientation.php`

Created a backed enum with two cases:
- `Orientation::Portrait` (value: 'P')
- `Orientation::Landscape` (value: 'L')

### 2. Updated PDFMerger Class
**File**: `src/PDFMerger/PDFMerger.php`

Modified all orientation-related methods to accept both `Orientation` enum and `string` for backward compatibility:

- `orientation(Orientation|string $orientation)`
- `addPDF(string $filePath, string|array $pages = 'all', Orientation|string|null $orientation = null)`
- `add()`, `addFile()`, `addAll()` - all updated with enum support
- `addString()` - updated with enum support
- `addMany()` - updated PHPDoc to reflect enum support
- `merge()` - updated with enum support
- `duplexMerge()` - updated with enum support
- `doMerge()` - internal method now handles enum to string conversion

### 3. Tests
**Files**: 
- `tests/Unit/OrientationEnumTest.php` (new)
- `tests/Unit/PDFMergerTest.php` (updated)

Added comprehensive tests for:
- Enum value verification
- Enum usage with `orientation()` method
- Enum usage with `addPDF()` method
- Enum usage with conditional methods

### 4. Documentation
**File**: `README.md`

Updated examples to show:
- Enum usage (recommended approach)
- String usage (still supported for backward compatibility)
- Named parameters with enum
- Per-file orientation with enum

### 5. Example Usage
**File**: `src/PDFMerger/examples/orientation_enum_usage.php`

Created comprehensive examples demonstrating:
- Default orientation with enum
- Per-file orientation
- Named parameters
- Conditional orientation
- Match expressions
- Backward compatibility

## Benefits

### 1. Type Safety
```php
// Before: Any string could be passed
$merger->orientation('X'); // Would not be caught until runtime

// After: Only valid enum cases are accepted
$merger->orientation(Orientation::Landscape); // ✓
$merger->orientation('invalid'); // Still works for BC, but enum is recommended
```

### 2. IDE Autocomplete
When typing `Orientation::`, IDEs will suggest:
- `Portrait`
- `Landscape`

This eliminates the need to remember whether it's 'P' or 'Portrait', 'L' or 'Landscape'.

### 3. Better Intent
```php
// Before
->orientation('L') // What does 'L' mean?

// After
->orientation(Orientation::Landscape) // Crystal clear!
```

### 4. Refactoring Safety
If you need to rename or change values, the enum provides a single source of truth that can be refactored safely across the codebase.

### 5. Pattern Matching
```php
$orientation = Orientation::Landscape;

$description = match ($orientation) {
    Orientation::Portrait => 'Portrait mode',
    Orientation::Landscape => 'Landscape mode',
};
```

## Backward Compatibility

All existing code continues to work without any changes:

```php
// Still works
PDFMerger::make()
    ->orientation('L')
    ->addPDF($file, 'all', 'P')
    ->merge()
    ->save();
```

The implementation accepts both `Orientation` enum and `string` types, ensuring a smooth migration path for existing users.

## Migration Guide

### For New Code
Use the enum for better developer experience:
```php
use StitchDigital\PDFMerger\Enums\Orientation;

PDFMerger::make()
    ->orientation(Orientation::Landscape)
    ->addPDF($file, orientation: Orientation::Portrait)
    ->merge()
    ->save();
```

### For Existing Code
No changes required. Your existing code will continue to work:
```php
PDFMerger::make()
    ->orientation('L')
    ->addPDF($file, 'all', 'P')
    ->merge()
    ->save();
```

You can gradually migrate to the enum as you update your code.

## Test Results

All tests pass (28 tests, 31 assertions):
- ✓ Enum value verification
- ✓ Enum usage with methods
- ✓ Backward compatibility with strings
- ✓ Type safety verification

Static analysis (PHPStan): No errors
Code formatting (Laravel Pint): All files formatted correctly
