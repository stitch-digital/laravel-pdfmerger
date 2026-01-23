# Security Policy

## Supported Versions

We release patches for security vulnerabilities in the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 2.x     | :white_check_mark: |
| 1.x     | :x:                |

## Reporting a Vulnerability

We take the security of Laravel PDFMerger seriously. If you discover a security vulnerability, please follow these steps:

### Please DO NOT:

- Open a public GitHub issue
- Disclose the vulnerability publicly before it has been addressed

### Please DO:

1. **Email us directly** at security@stitchdigital.com with:
   - A description of the vulnerability
   - Steps to reproduce the issue
   - Potential impact of the vulnerability
   - Any suggested fixes (if you have them)

2. **Use the subject line**: "Security Vulnerability in Laravel PDFMerger"

3. **Allow us time to respond**:
   - We will acknowledge your email within 48 hours
   - We will provide a detailed response within 7 days
   - We will work with you to understand and resolve the issue

### What to Expect

After you report a vulnerability:

1. **Acknowledgment**: We'll confirm receipt of your report
2. **Assessment**: We'll assess the vulnerability and its impact
3. **Resolution**: We'll develop and test a fix
4. **Disclosure**: We'll coordinate with you on public disclosure
5. **Credit**: We'll credit you in the security advisory (unless you prefer to remain anonymous)

## Security Update Process

When a security vulnerability is confirmed:

1. We'll prepare a fix in a private repository
2. We'll release a new version with the security patch
3. We'll publish a security advisory on GitHub
4. We'll update the CHANGELOG with security fix details
5. We'll notify users through appropriate channels

## Security Best Practices

When using Laravel PDFMerger:

### Input Validation

Always validate file paths and user input:

```php
// Good - Validate file exists and is readable
if (!file_exists($filePath) || !is_readable($filePath)) {
    throw new \Exception('Invalid file');
}

PDFMerger::make()->addPDF($filePath);
```

### File Permissions

Ensure proper file permissions on merged PDFs:

```php
// Set appropriate permissions after saving
$merger->save($outputPath);
chmod($outputPath, 0644);
```

### Temporary Files

The package automatically cleans up temporary files, but ensure your `storage/tmp/` directory has appropriate permissions and is not web-accessible.

### User-Uploaded PDFs

If accepting PDF uploads from users:

```php
// Validate file type
$allowedMimeTypes = ['application/pdf'];
if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
    throw new \Exception('Invalid file type');
}

// Scan for malware if applicable
// ... your malware scanning logic ...

// Process the PDF
PDFMerger::make()->addPDF($file->path());
```

### Memory Limits

Be cautious with large PDFs to prevent memory exhaustion:

```php
// Configure memory limit in config/pdfmerger.php
'memory_limit' => 256, // MB
```

## Dependency Security

We use automated tools to monitor dependencies:

- **Dependabot** for automated dependency updates
- **Composer audit** for known vulnerabilities
- **GitHub Actions** for automated security checks

## Disclosure Policy

We follow **Coordinated Disclosure**:

1. Security issues are fixed privately
2. Public disclosure occurs after a fix is released
3. We credit researchers who report issues responsibly

## Contact

For security concerns: security@stitchdigital.com

For general issues: Use [GitHub Issues](https://github.com/stitch-digital/laravel-pdfmerger/issues)

## Hall of Fame

We thank the following security researchers for responsible disclosure:

- *No vulnerabilities reported yet*

---

Thank you for helping keep Laravel PDFMerger secure!
