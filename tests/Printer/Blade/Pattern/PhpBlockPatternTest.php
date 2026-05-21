<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use ViewConverter\Printer\Blade\Pattern\PhpBlockPattern;
use PHPUnit\Framework\TestCase;

class PhpBlockPatternTest extends TestCase
{
    private PhpBlockPattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new PhpBlockPattern();
    }

    public function testPhpBlockIsReplacedWithComment(): void
    {
        $input  = "@php\n\$x = 1;\n@endphp";
        $this->assertSame('{# PHP block - review manually #}', $this->pattern->apply($input));
    }

    public function testHtmlOutsidePhpBlockIsUntouched(): void
    {
        $input = "<p>Hello</p>\n@php\$x = 1;@endphp\n<p>World</p>";
        $this->assertStringContainsString('<p>Hello</p>', $this->pattern->apply($input));
        $this->assertStringContainsString('<p>World</p>', $this->pattern->apply($input));
    }
}
