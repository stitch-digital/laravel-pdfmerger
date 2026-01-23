<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Temporary File Storage Path
    |--------------------------------------------------------------------------
    |
    | The directory where temporary PDF files will be stored during merge
    | operations. These files are automatically cleaned up after use.
    |
    */
    'temp_path' => storage_path('tmp/pdfmerger'),

    /*
    |--------------------------------------------------------------------------
    | Default Output Path
    |--------------------------------------------------------------------------
    |
    | The default directory where merged PDF files will be saved if no
    | specific path is provided to the save() method.
    |
    */
    'output_path' => storage_path('app/pdfs'),

    /*
    |--------------------------------------------------------------------------
    | Default Orientation
    |--------------------------------------------------------------------------
    |
    | The default page orientation for merged PDFs. Can be overridden per
    | file. Options: 'P' (Portrait) or 'L' (Landscape)
    |
    */
    'orientation' => 'P',

    /*
    |--------------------------------------------------------------------------
    | Duplex Mode
    |--------------------------------------------------------------------------
    |
    | Enable duplex mode by default. When enabled, blank pages are added
    | between documents to support double-sided printing.
    |
    */
    'duplex' => false,

    /*
    |--------------------------------------------------------------------------
    | Memory Limit
    |--------------------------------------------------------------------------
    |
    | The memory limit (in megabytes) for processing large PDF files.
    | Increase this value if you're working with very large documents.
    |
    */
    'memory_limit' => 256,

    /*
    |--------------------------------------------------------------------------
    | Allow URLs
    |--------------------------------------------------------------------------
    |
    | Enable or disable the ability to add PDFs from remote URLs.
    | When enabled, the package can download PDFs from http://, https://,
    | or ftp:// URLs. Disable this in security-sensitive environments.
    |
    */
    'allow_urls' => true,

    /*
    |--------------------------------------------------------------------------
    | URL Download Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout (in seconds) for downloading PDFs from remote URLs.
    | Increase this value if you're working with large files or slow networks.
    |
    */
    'url_download_timeout' => 30,

    /*
    |--------------------------------------------------------------------------
    | URL Verify SSL
    |--------------------------------------------------------------------------
    |
    | Whether to verify SSL certificates when downloading PDFs from HTTPS URLs.
    | It's recommended to keep this enabled for security. Only disable in
    | development environments if working with self-signed certificates.
    |
    */
    'url_verify_ssl' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Storage Disk
    |--------------------------------------------------------------------------
    |
    | The default Laravel Storage disk to use for file operations when
    | using Storage facade integration methods.
    |
    */
    'disk' => 'local',
];
