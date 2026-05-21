<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use ViewConverter\Printer\Blade\Pattern\MiscPattern;
use PHPUnit\Framework\TestCase;

class MiscPatternTest extends TestCase
{
    private MiscPattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new MiscPattern();
    }

    public function testVerbatimBlockIsConverted(): void
    {
        $input  = "@verbatim\n{{ raw }}\n@endverbatim";
        $output = $this->pattern->apply($input);

        $this->assertStringContainsString('{% verbatim %}', $output);
        $this->assertStringContainsString('{% endverbatim %}', $output);
    }

    public function testJsonConvertsToJsonEncodeFilter(): void
    {
        $this->assertSame('{{ data|json_encode }}', $this->pattern->apply('@json($data)'));
    }

    public function testCsrfConvertsToComment(): void
    {
        $this->assertStringContainsString('{# @csrf', $this->pattern->apply('@csrf'));
    }

    public function testMethodConvertsToComment(): void
    {
        $this->assertStringContainsString('{# @method', $this->pattern->apply("@method('PUT')"));
    }
}
