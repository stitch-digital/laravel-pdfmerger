# Changelog

All notable changes to `stitch-digital/laravel-pdfmerger` will be documented in this file.

Updates follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [Unreleased]

## [2.0.0] (2026-01-23)

### 🎉 Major Modernization Release

Version 2.0 is a complete modernization of the package with significant improvements to developer experience, code quality, and maintainability.

### ⚠️ Breaking Changes

- **PHP 8.2+ Required**: Minimum PHP version increased from 5.5.9 to 8.2
- **Laravel 9+ Required**: Minimum Laravel version increased from 5.0 to 9.0
- **Strict Types**: All files now use `declare(strict_types=1)` 
- **Type Declarations**: Full type hints and return types on all methods
- **Method Returns**: `merge()` and `duplexMerge()` now return `self` instead of `void` for method chaining
- **Property Names**: Internal properties renamed to remove Hungarian notation (e.g., `$oFilesystem` → `$filesystem`)
- **Exceptions**: Custom exception classes replace generic `\Exception`:
  - `PDFNotFoundException` for missing files
  - `InvalidPagesException` for invalid page parameters
  - `PDFMergeException` for merge failures
- **Travis CI Removed**: Replaced with GitHub Actions

### ✨ Added

#### Developer Experience (DX) Improvements
- **Modern Fluent API**: Complete method chaining support throughout
- **`make()` Factory Method**: Replace `init()` with Laravel-style `make()` (Filament pattern)
- **`new()` Factory Method**: Alternative factory method alias
- **Named Parameters**: Full PHP 8.0+ named parameter support
- **Conditional Methods**: `when()` and `unless()` for conditional operations
- **Tap Support**: `tap()` method for side effects
- **Macro Support**: `Macroable` trait for extensibility

#### New Methods
- `orientation(string)`: Set default orientation fluently
- `duplex(bool)`: Enable/disable duplex mode fluently
- `reset()`: Reset merger instance to initial state
- `add()`: Alias for `addPDF()`
- `addFile()`: Alias for `addPDF()`
- `addAll()`: Shorthand for adding all pages
- `addMany(iterable)`: Add multiple PDFs at once
- `toResponse()`: Return as Response object
- `toBase64()`: Return as base64 encoded string
- `saveAs(string)`: Clearer alias for `save()`

#### Infrastructure & Quality
- **GitHub Actions CI**: Complete CI/CD pipeline with matrix testing
  - Tests across PHP 8.2, 8.3, 8.4
  - Tests across Laravel 9, 10, 11
  - Tests with prefer-lowest and prefer-stable dependencies
- **Code Quality Workflows**: Automated style and analysis checks
- **Security Scanning**: Automated dependency auditing
- **Laravel Pint**: Code style enforcement (Laravel preset)
- **PHPStan Level 8**: Strict static analysis
- **Psalm Level 5**: Additional static analysis with security scanning
- **Comprehensive Test Suite**: Unit and feature tests with Orchestra Testbench
- **PHPUnit 10/11**: Modern testing framework

#### Configuration & Documentation
- **Configuration File**: `config/pdfmerger.php` with sensible defaults
- **Comprehensive README**: Modern examples, badges, full API documentation
- **CONTRIBUTING.md**: Detailed contribution guidelines
- **SECURITY.md**: Security policy and vulnerability reporting
- **UPGRADING.md**: Complete migration guide from v1.x
- **.editorconfig**: Standard editor configuration
- **.gitattributes**: Export-ignore configuration
- **Dependabot**: Automated dependency updates

#### Traits
- `Conditionable`: Adds `when()` and `unless()` methods
- `Macroable`: Allows custom method extensions
- `Tappable`: Adds `tap()` method

### 🔧 Changed

- Updated minimum dependencies:
  - `php`: `^8.2`
  - `illuminate/support`: `^9.0|^10.0|^11.0`
  - `illuminate/http`: `^9.0|^10.0|^11.0`
  - `illuminate/filesystem`: `^9.0|^10.0|^11.0`
  - `setasign/fpdi`: `^2.6` (includes security fixes)
  - `setasign/fpdf`: `^1.8`
- Dev dependencies modernized:
  - `phpunit/phpunit`: `^10.5|^11.0`
  - `orchestra/testbench`: `^8.0|^9.0`
  - Added `laravel/pint`: `^1.0`
  - Added `phpstan/phpstan`: `^1.10`
  - Added `vimeo/psalm`: `^5.0`
  - Removed outdated `scrutinizer/ocular`
  - Removed outdated `squizlabs/php_codesniffer`
- Service provider now publishes configuration
- Facade includes comprehensive PHPDoc annotations for IDE support
- Updated namespace for tests: `StitchDigital\PDFMerger\Tests`

### 🐛 Fixed

- Property initialization bugs (class names as strings instead of actual instances)
- Temporary file cleanup in destructor now checks file existence
- Better error messages with context
- Validation for page numbers (must be positive integers)
- Exception handling around FPDI operations

### 📚 Documentation

- Complete README rewrite with modern examples
- All public methods have PHPDoc comments
- Inline code examples using modern PHP syntax
- Error handling examples
- Macro extension examples
- Migration guide from v1.x

### 🗑️ Deprecated

- `init()` method - Use `make()` instead (will be removed in v3.0)

### 🔒 Security

- Automated security scanning via GitHub Actions
- Dependabot for dependency updates
- Composer audit integration
- Updated to latest FPDI version with security fixes

---

## [1.3.2] (2024-07-02)

### Added
- Support Auto-Discovery #47 (thanks @hasanwijaya)

### Fixed 
- No response being send on download #31 #32 (thanks @KreutzerCode)

## [1.3.1] (2022-09-14)

### Fixed
- Don't force portrait orientation during merge if not specified (thanks @Jason-Toh)
 
## [1.3.0] (2021-10-25)

### Fixed
- String (and Array) Helper functions are now removed in Laravel #17 (thanks @warksit)
 
## [1.2.0] (2021-10-21)

### Fixed
- Replace itbz (deprecated) with setasign #14 #15 (thanks @laraben)

### Added
- auto orientation #11 (thanks @laraben)
 
## [1.1.0] (2018-05-29)

### Added
 - Added `duplexMerge` to support duplex-safe merging 

### Fixed
 - Basic PDF library changed

## [1.0.0] (2017-02-17)

### Added
- new laravel-pdfmerger package

