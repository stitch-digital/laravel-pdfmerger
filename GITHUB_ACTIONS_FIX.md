# GitHub Actions CI Fixes

This document explains the fixes applied to resolve GitHub Actions test failures.

## Problem 1: FPDF Compatibility with PHP 8.0+

### Error
```
Error: Call to undefined function get_magic_quotes_runtime()
```

### Root Cause
The FPDF library (v1.8.6) uses deprecated PHP functions `get_magic_quotes_runtime()` and `get_magic_quotes_gpc()` which were removed in PHP 8.0.

### Solution
Created a polyfill file `src/PDFMerger/polyfills.php` that provides compatibility shims:

```php
if (! function_exists('get_magic_quotes_runtime')) {
    function get_magic_quotes_runtime(): bool
    {
        return false;
    }
}
```

The polyfill is automatically loaded via Composer's autoload files configuration.

### Files Changed
- ✅ Created `src/PDFMerger/polyfills.php`
- ✅ Updated `composer.json` to include the polyfill in autoload files

---

## Problem 2: Orchestra Testbench Version Compatibility

### Error
```
Error: Access to undeclared static property $latestResponse
```

### Root Cause
The GitHub Actions workflow was trying to use Orchestra Testbench 7.x (for Laravel 9) with PHPUnit 10, which are incompatible. Orchestra Testbench 7 only supports PHPUnit 9.

### Solution
Simplified the support matrix to focus on actively maintained Laravel versions:

- ✅ **Dropped Laravel 9 support** (reached end-of-life February 2024)
- ✅ **Support Laravel 10 with Orchestra Testbench 8.22+** (PHPUnit 10 compatible)
- ✅ **Support Laravel 11 with Orchestra Testbench 9.0+** (PHPUnit 11 compatible)

### Files Changed
- ✅ Updated `composer.json` to require Laravel 10+ and Orchestra Testbench 8/9/10
- ✅ Updated `.github/workflows/tests.yml` to test Laravel 10 and 11 only
- ✅ Updated `README.md` to reflect Laravel 10+ requirement
- ✅ Updated `CHANGELOG.md` to document the version requirement change
- ✅ Updated `UPGRADING.md` to clarify Laravel 10+ is required

---

## Version Compatibility Matrix

| Laravel | Orchestra Testbench | PHPUnit | PHP    |
|---------|---------------------|---------|--------|
| 10.x    | 8.22+               | 10.5+   | 8.2+   |
| 11.x    | 9.0+                | 11.0+   | 8.2+   |

---

## GitHub Actions Test Matrix

The updated workflow now tests:

- **PHP Versions**: 8.2, 8.3, 8.4
- **Laravel Versions**: 10.x, 11.x
- **Dependency Strategies**: prefer-lowest, prefer-stable

Total test combinations: **12 matrix jobs**

---

## Verification

All quality checks passing locally:

```bash
✅ composer test          # 28 tests, 31 assertions
✅ composer analyse       # PHPStan Level 8, no errors
✅ composer format-check  # Laravel Pint, no issues
```

## Expected GitHub Actions Status

With these fixes, all GitHub Actions workflows should pass:

- ✅ **Tests workflow**: All PHP/Laravel combinations
- ✅ **Code Quality workflow**: Pint and PHPStan checks
- ✅ **Security workflow**: Composer audit

---

## Migration Notes for Users

Users upgrading to v2.0 should be aware:

1. **PHP 8.2+ required** - No longer supports PHP 5.5-8.1
2. **Laravel 10+ required** - Laravel 9 and earlier are not supported
3. **Modern dependencies** - All dependencies updated to current versions
4. **Breaking API changes** - See `UPGRADING.md` for full migration guide

---

## Related Documentation

- `CHANGELOG.md` - Complete list of changes in v2.0
- `UPGRADING.md` - Step-by-step migration guide
- `README.md` - Updated installation and usage instructions
- `CONTRIBUTING.md` - How to contribute with modern tooling
