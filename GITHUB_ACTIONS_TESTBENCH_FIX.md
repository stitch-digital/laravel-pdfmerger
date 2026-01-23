# GitHub Actions Fix - Orchestra Testbench Compatibility

## Issue

GitHub Actions tests were failing with this error across all 43 tests:

```
Error: Access to undeclared static property $latestResponse
```

This was occurring in Orchestra Testbench 8.x (specifically versions before 8.23.1) which had a bug where the `$latestResponse` property was being accessed before being declared.

## Root Cause

The issue was caused by:

1. **Orchestra Testbench version constraint** - The workflow specified `^8.23` but the bug fix was in 8.23.1
2. **Prefer-lowest dependency strategy** - When GitHub Actions ran with `prefer-lowest`, it installed 8.23.0 which still contained the bug

## Solution

### 1. Updated Orchestra Testbench Version Constraint

**File**: `composer.json`

Changed from:
```json
"orchestra/testbench": "^8.23|^9.0|^10.0"
```

To:
```json
"orchestra/testbench": "^8.23.1|^9.0|^10.0"
```

This ensures that even with `prefer-lowest`, composer will install at least version 8.23.1 which includes the fix.

### 2. Updated GitHub Actions Workflow

**File**: `.github/workflows/tests.yml`

Changed from:
```yaml
- laravel: '10.*'
  testbench: '^8.23'
```

To:
```yaml
- laravel: '10.*'
  testbench: '^8.23.1'
```

### 3. Simplified TestCase

**File**: `tests/TestCase.php`

Removed the complex workaround for the Orchestra Testbench bug since we now require a version that includes the fix. The TestCase is now clean and straightforward:

```php
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
            'PDFMerger' => \StitchDigital\PDFMerger\Facades\PDFMergerFacade::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default environment configuration
    }

    protected function getFixturePath(string $filename): string
    {
        return __DIR__.'/Fixtures/'.$filename;
    }
}
```

## Verification

All tests now pass locally:
```
Tests: 43, Assertions: 54
Code Coverage: 71.33%
```

Code quality checks pass:
- ✅ Laravel Pint (code formatting)
- ✅ PHPStan Level 8 (static analysis)

## Expected GitHub Actions Behavior

With these changes, all GitHub Actions test matrix jobs should now pass:

- **PHP versions**: 8.2, 8.3, 8.4
- **Laravel versions**: 10.x, 11.x  
- **Dependency strategies**: prefer-lowest, prefer-stable
- **Total combinations**: 12 matrix jobs

The fix ensures that even when using `prefer-lowest`, the minimum testbench version 8.23.1 (with the bug fix) will be installed.

## Related Issue

This was documented in Orchestra Testbench's changelog:
- **Bug**: `$latestResponse` property accessed before declaration
- **Fixed in**: v8.23.1
- **Release date**: June 2024

## Files Changed

1. `composer.json` - Updated testbench version constraint to `^8.23.1`
2. `.github/workflows/tests.yml` - Updated testbench requirement to `^8.23.1`
3. `tests/TestCase.php` - Removed workaround, simplified implementation
