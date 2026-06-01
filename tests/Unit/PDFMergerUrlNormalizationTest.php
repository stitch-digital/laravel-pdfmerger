<?php

declare(strict_types=1);

namespace StitchDigital\PDFMerger\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use StitchDigital\PDFMerger\PDFMerger;
use StitchDigital\PDFMerger\Tests\TestCase;

class PDFMergerUrlNormalizationTest extends TestCase
{
    protected PDFMerger $merger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->merger = new PDFMerger(new Filesystem);
    }

    #[Test]
    public function it_normalizes_urls_with_single_spaces(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('normalizeUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->merger, 'https://example.com/path with spaces/file.pdf');
        $this->assertEquals('https://example.com/path%20with%20spaces/file.pdf', $result);
    }

    #[Test]
    public function it_normalizes_urls_with_multiple_consecutive_spaces(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('normalizeUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->merger, 'https://example.com/pdfs/  01.pdf');
        $this->assertEquals('https://example.com/pdfs/%2001.pdf', $result);
    }

    #[Test]
    public function it_trims_whitespace_from_url_ends(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('normalizeUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->merger, '  https://example.com/file.pdf  ');
        $this->assertEquals('https://example.com/file.pdf', $result);
    }

    #[Test]
    public function it_normalizes_urls_with_tabs_and_newlines(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('normalizeUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->merger, "https://example.com/file\t.pdf");
        $this->assertEquals('https://example.com/file%20.pdf', $result);
    }

    #[Test]
    public function it_leaves_properly_encoded_urls_unchanged(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('normalizeUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->merger, 'https://example.com/file%20name.pdf');
        $this->assertEquals('https://example.com/file%20name.pdf', $result);
    }

    #[Test]
    public function it_handles_urls_without_paths(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('normalizeUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->merger, 'https://example.com');
        $this->assertEquals('https://example.com', $result);
    }

    #[Test]
    public function it_handles_urls_with_query_strings(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('normalizeUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->merger, 'https://example.com/file name.pdf?version=1');
        $this->assertEquals('https://example.com/file%20name.pdf?version=1', $result);
    }

    #[Test]
    public function it_validates_normalized_urls_correctly(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('isUrl');
        $method->setAccessible(true);

        // URL with spaces should be detected as valid after normalization
        $this->assertTrue($method->invoke($this->merger, 'https://example.com/file name.pdf'));
        $this->assertTrue($method->invoke($this->merger, 'https://example.com/pdfs/  01.pdf'));
    }

    #[Test]
    public function it_still_detects_non_urls(): void
    {
        $reflection = new \ReflectionClass($this->merger);
        $method = $reflection->getMethod('isUrl');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($this->merger, '/path/to/file.pdf'));
        $this->assertFalse($method->invoke($this->merger, 'relative/path/file.pdf'));
        $this->assertFalse($method->invoke($this->merger, 'not a url'));
    }
}
