<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Blade\Pattern\CommentsPattern;

class CommentsPatternTest extends TestCase
{
    private CommentsPattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new CommentsPattern();
    }

    public function testBladeCommentConvertsToTwigComment(): void
    {
        $this->assertSame('{# this is a comment #}', $this->pattern->apply('{{-- this is a comment --}}'));
    }

    public function testMultilineCommentIsConverted(): void
    {
        $input = "{{-- line one\nline two --}}";
        $expected = "{# line one\nline two #}";
        $this->assertSame($expected, $this->pattern->apply($input));
    }

    public function testNonCommentContentIsUntouched(): void
    {
        $input = '<div>{{ $name }}</div>';
        $this->assertSame($input, $this->pattern->apply($input));
    }
}
