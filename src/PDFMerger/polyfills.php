<?php

declare(strict_types=1);

/**
 * Polyfills for deprecated PHP functions removed in PHP 8.0+
 * Required for compatibility with older libraries like FPDF
 */
if (! function_exists('get_magic_quotes_runtime')) {
    /**
     * Polyfill for get_magic_quotes_runtime() removed in PHP 8.0
     * Always returns false as magic quotes were removed
     */
    function get_magic_quotes_runtime(): bool
    {
        return false;
    }
}

if (! function_exists('get_magic_quotes_gpc')) {
    /**
     * Polyfill for get_magic_quotes_gpc() removed in PHP 8.0
     * Always returns false as magic quotes were removed
     */
    function get_magic_quotes_gpc(): bool
    {
        return false;
    }
}
