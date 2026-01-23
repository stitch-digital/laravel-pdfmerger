# Upgrading from v1.x to v2.0

This guide will help you upgrade from Laravel PDFMerger v1.x to v2.0.

## Overview

Version 2.0 is a major modernization release that brings:

- PHP 8.2+ with strict types
- Laravel 9-11 support
- Modern fluent API
- Full type safety
- Improved error handling
- Better developer experience

## Breaking Changes

### 1. PHP Version Requirement

**Before (v1.x):**
- PHP >= 5.5.9

**After (v2.0):**
- PHP >= 8.2

**Action Required:**
- Upgrade your PHP version to 8.2 or higher
- Update `composer.json` PHP requirement

### 2. Laravel Version Support

**Before (v1.x):**
- Laravel >= 5.0

**After (v2.0):**
- Laravel 9.x, 10.x, or 11.x

**Action Required:**
- Upgrade to Laravel 9 or higher
- For Laravel 11, no manual provider registration needed (auto-discovery)

### 3. API Changes

#### init() Replaced with make()

**Before (v1.x):**
```php
use StitchDigital\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

$merger = PDFMerger::init();
$merger->addPDF($file1);
$merger->addPDF($file2);
$merger->merge();
$merger->save('output.pdf');
```

**After (v2.0):**
```php
use StitchDigital\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

PDFMerger::make()
    ->addPDF($file1)
    ->addPDF($file2)
    ->merge()
    ->save('output.pdf');
```

**Migration:**
- Replace `init()` with `make()`
- `init()` is still available but deprecated
- Use method chaining for cleaner code

#### merge() Now Returns self

**Before (v1.x):**
```php
$merger->merge(); // Returns void
$merger->save();
```

**After (v2.0):**
```php
$merger->merge()->save(); // merge() returns self for chaining
```

**Migration:**
- No code changes required
- You can now chain methods after `merge()`

#### duplexMerge() Now Returns self

**Before (v1.x):**
```php
$merger->duplexMerge();
$merger->save();
```

**After (v2.0):**
```php
$merger->duplexMerge()->save(); // Chainable
```

**Migration:**
- No code changes required
- You can now chain methods after `duplexMerge()`

### 4. Exception Changes

**Before (v1.x):**
- Generic `\Exception` thrown

**After (v2.0):**
- Specific exception classes:
  - `PDFNotFoundException`
  - `InvalidPagesException`
  - `PDFMergeException`

**Migration:**
```php
// Before
try {
    $merger->addPDF($file);
} catch (\Exception $e) {
    // Handle
}

// After
use StitchDigital\PDFMerger\Exceptions\PDFNotFoundException;
use StitchDigital\PDFMerger\Exceptions\InvalidPagesException;
use StitchDigital\PDFMerger\Exceptions\PDFMergeException;

try {
    $merger->addPDF($file);
} catch (PDFNotFoundException $e) {
    // File not found
} catch (InvalidPagesException $e) {
    // Invalid pages
} catch (PDFMergeException $e) {
    // Merge error
}
```

### 5. Type Safety

**Before (v1.x):**
- No type hints
- No return types
- Mixed parameter types

**After (v2.0):**
- Full type hints on all methods
- Return type declarations
- Strict types enabled

**Migration:**
- Ensure you're passing correct types:
  - `$pages` must be `string|array` (not numeric)
  - `$orientation` must be `string|null`
  - Method returns are now typed

```php
// Before - may work with loose types
$merger->addPDF($file, 1); // Might accept integer

// After - strict types required
$merger->addPDF($file, [1]); // Must be array or 'all'
```

### 6. Internal Property Names (If Extending)

If you've extended the `PDFMerger` class:

**Before (v1.x):**
```php
protected $oFilesystem;
protected $oFPDI;
protected $aFiles;
```

**After (v2.0):**
```php
protected Filesystem $filesystem;
protected FPDI $fpdi;
protected Collection $files;
```

**Migration:**
- Update any property references
- Add property type declarations
- Use modern naming conventions

## New Features in v2.0

### 1. Fluent API

```php
PDFMerger::make()
    ->orientation('L')
    ->duplex(true)
    ->addPDF($file1)
    ->addPDF($file2)
    ->merge()
    ->save();
```

### 2. Named Parameters (PHP 8.0+)

```php
PDFMerger::make()->addPDF(
    filePath: '/path/to/file.pdf',
    pages: [1, 3, 5],
    orientation: 'L'
);
```

### 3. Conditional Methods

```php
PDFMerger::make()
    ->when($condition, fn($m) => $m->addPDF($file))
    ->unless($otherCondition, fn($m) => $m->duplex(true));
```

### 4. Method Aliases

```php
$merger->add($file);        // Alias for addPDF()
$merger->addFile($file);    // Alias for addPDF()
$merger->addAll($file);     // Shorthand for all pages
```

### 5. Additional Output Methods

```php
$merger->toResponse();  // Returns Response object
$merger->toBase64();    // Returns base64 string
$merger->saveAs($path); // Clearer alias for save()
```

### 6. Macro Support

```php
use StitchDigital\PDFMerger\PDFMerger;

PDFMerger::macro('addDirectory', function ($directory) {
    foreach (glob($directory . '/*.pdf') as $file) {
        $this->addPDF($file);
    }
    return $this;
});
```

### 7. Configuration File

```bash
php artisan vendor:publish --tag=pdfmerger-config
```

## Step-by-Step Migration

### Step 1: Update Dependencies

Update your `composer.json`:

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^9.0|^10.0|^11.0",
        "stitch-digital/laravel-pdfmerger": "^2.0"
    }
}
```

Run:
```bash
composer update stitch-digital/laravel-pdfmerger
```

### Step 2: Update PHP Version

Ensure your server runs PHP 8.2+:

```bash
php -v
```

### Step 3: Update Code

Replace `init()` with `make()`:

```bash
# Use find/replace in your editor
find: PDFMerger::init()
replace: PDFMerger::make()
```

### Step 4: Add Type Hints (Optional but Recommended)

If you have wrapper methods:

```php
// Before
public function mergePDFs($files)
{
    // ...
}

// After
public function mergePDFs(array $files): bool
{
    // ...
}
```

### Step 5: Update Exception Handling

Update catch blocks to use specific exceptions:

```php
use StitchDigital\PDFMerger\Exceptions\{
    PDFNotFoundException,
    InvalidPagesException,
    PDFMergeException
};
```

### Step 6: Test Thoroughly

```bash
# Run your test suite
composer test

# Test the actual PDF merging functionality
```

## Common Issues and Solutions

### Issue: "init() is deprecated"

**Solution:** Replace with `make()`. The `init()` method still works but will be removed in v3.0.

### Issue: Type errors when passing parameters

**Solution:** Ensure correct types:
```php
// Wrong
->addPDF($file, 1)

// Correct
->addPDF($file, [1])
```

### Issue: Undefined property errors

**Solution:** If extending the class, update property names and add type declarations.

### Issue: Tests failing after upgrade

**Solution:** 
- Update PHPUnit to ^10.5 or ^11.0
- Update test assertions for typed returns
- Check for hardcoded type assumptions

## Deprecations

The following are deprecated and will be removed in v3.0:

- `init()` method - Use `make()` instead

## Need Help?

- Check the [README](README.md) for updated examples
- Read the [CONTRIBUTING](CONTRIBUTING.md) guide
- Open an issue on [GitHub](https://github.com/stitch-digital/laravel-pdfmerger/issues)
- Review the [CHANGELOG](CHANGELOG.md) for detailed changes

## Timeline

- **v1.x**: End of life, security fixes only
- **v2.x**: Current stable version, active development
- **v3.x**: Planned for future, will remove deprecations
