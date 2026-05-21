<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use ViewConverter\Printer\Blade\Pattern\LoopsPattern;
use PHPUnit\Framework\TestCase;

class LoopsPatternTest extends TestCase
{
    private LoopsPattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new LoopsPattern();
    }

    public function testForeachConvertsToTwigFor(): void
    {
        $input  = "@foreach(\$items as \$item)\n    x\n@endforeach";
        $output = $this->pattern->apply($input);

        $this->assertStringContainsString('{% for item in items %}', $output);
        $this->assertStringContainsString('{% endfor %}', $output);
    }

    public function testKeyValueForeachIsConverted(): void
    {
        $input  = "@foreach(\$items as \$key => \$value)\n    x\n@endforeach";
        $output = $this->pattern->apply($input);

        $this->assertStringContainsString('{% for key, value in items %}', $output);
    }

    public function testForLoopFallsBackToComment(): void
    {
        $output = $this->pattern->apply('@for ($i = 0; $i < 10; $i++)');
        $this->assertStringContainsString('{# TODO: @for(', $output);
    }

    public function testWhileLoopFallsBackToComment(): void
    {
        $output = $this->pattern->apply('@while($running)');
        $this->assertStringContainsString('{# TODO: while (', $output);
    }

    public function testEndwhileConvertsToComment(): void
    {
        $this->assertSame('{# endwhile #}', $this->pattern->apply('@endwhile'));
    }
}
