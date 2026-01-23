# Contributing to Laravel PDFMerger

Thank you for considering contributing to Laravel PDFMerger! This document outlines the process for contributing to this project.

## Code of Conduct

We expect all contributors to be respectful and professional. Please be kind and courteous in all interactions.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include:

- A clear and descriptive title
- Detailed steps to reproduce the issue
- Expected behavior vs actual behavior
- PHP and Laravel versions
- Any error messages or logs
- Code samples if applicable

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, include:

- A clear and descriptive title
- Detailed description of the proposed functionality
- Examples of how it would be used
- Why this enhancement would be useful

### Pull Requests

1. **Fork the repository** and create your branch from `master`
2. **Install dependencies**: `composer install`
3. **Make your changes** following our coding standards
4. **Add tests** for any new functionality
5. **Ensure tests pass**: `composer test`
6. **Check code style**: `composer format-check`
7. **Run static analysis**: `composer analyse`
8. **Update documentation** if needed
9. **Commit your changes** with clear, descriptive messages
10. **Push to your fork** and submit a pull request

## Development Setup

```bash
# Clone your fork
git clone https://github.com/your-username/laravel-pdfmerger.git
cd laravel-pdfmerger

# Install dependencies
composer install

# Run tests
composer test

# Check code style
composer format-check

# Fix code style automatically
composer format

# Run static analysis
composer analyse
```

## Coding Standards

This project follows strict coding standards to maintain code quality:

### PHP Standards

- **PHP 8.2+** syntax and features
- **Strict types** declaration in all files: `declare(strict_types=1)`
- **Type hints** for all method parameters
- **Return types** for all methods
- **Property types** for all class properties
- **PSR-12** coding style (enforced by Laravel Pint)

### Laravel Conventions

- Follow Laravel naming conventions
- Use fluent, chainable APIs where appropriate
- Return `self` from methods that modify state
- Use Laravel's helper functions and collections

### Code Style

We use **Laravel Pint** for code style enforcement:

```bash
# Check code style
composer format-check

# Automatically fix code style
composer format
```

Configuration is in `pint.json`.

### Static Analysis

We use **PHPStan** (level 8) and **Psalm** (level 5) for static analysis:

```bash
# Run both analyzers
composer analyse

# Run individually
composer phpstan
composer psalm
```

Configurations are in `phpstan.neon` and `psalm.xml`.

## Testing

All code contributions must include tests. We use PHPUnit with Orchestra Testbench.

### Writing Tests

- Place unit tests in `tests/Unit/`
- Place feature/integration tests in `tests/Feature/`
- Use descriptive test method names
- Follow the Arrange-Act-Assert pattern
- Test both success and failure scenarios

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage report
composer test-coverage

# Run specific test file
vendor/bin/phpunit tests/Unit/PDFMergerTest.php

# Run specific test method
vendor/bin/phpunit --filter test_method_name
```

### Test Coverage

We aim for >80% code coverage. Coverage reports are generated in the `coverage/` directory.

## Documentation

- Update the README.md if you change functionality
- Add PHPDoc comments to all public methods
- Include code examples for new features
- Update CHANGELOG.md following Keep a Changelog format

## Git Commit Messages

- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit first line to 72 characters or less
- Reference issues and pull requests after the first line

### Commit Message Examples

```
Add support for custom page ranges

- Implement page range parsing
- Add tests for range functionality
- Update documentation

Closes #123
```

```
Fix memory leak in temporary file cleanup

The destructor was not properly cleaning up temporary files
in certain error conditions.

Fixes #456
```

## Branch Naming

- `feature/description` - New features
- `fix/description` - Bug fixes
- `docs/description` - Documentation changes
- `refactor/description` - Code refactoring
- `test/description` - Test additions/changes

## Review Process

1. All pull requests require review before merging
2. CI checks (tests, code quality, static analysis) must pass
3. Address review feedback promptly
4. Keep pull requests focused on a single concern
5. Squash commits if requested before merging

## Release Process

Releases follow [Semantic Versioning](https://semver.org/):

- **MAJOR**: Incompatible API changes
- **MINOR**: Backwards-compatible functionality additions
- **PATCH**: Backwards-compatible bug fixes

## Questions?

Feel free to open an issue with the `question` label if you need help or clarification.

## License

By contributing to Laravel PDFMerger, you agree that your contributions will be licensed under the MIT License.
