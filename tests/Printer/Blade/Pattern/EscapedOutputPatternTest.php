<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Blade\Pattern\EscapedOutputPattern;

class EscapedOutputPatternTest extends TestCase
{
    private EscapedOutputPattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new EscapedOutputPattern();
    }

    public function testSimpleVariableStripsDollarSign(): void
    {
        $this->assertSame('{{ name }}', $this->pattern->apply('{{ $name }}'));
    }

    public function testPropertyAccessConvertsArrowToDot(): void
    {
        $this->assertSame('{{ user.name }}', $this->pattern->apply('{{ $user->name }}'));
    }

    public function testArrayAccessConvertsToTwigDot(): void
    {
        $this->assertSame("{{ config.key }}", $this->pattern->apply("{{ \$config['key'] }}"));
    }

    public function testNestedPropertyChainIsConverted(): void
    {
        $this->assertSame('{{ user.profile.avatar }}', $this->pattern->apply('{{ $user->profile->avatar }}'));
    }

    public function testLoopIndexVariableIsConverted(): void
    {
        $this->assertSame('{{ loop.index0 }}', $this->pattern->apply('{{ $loop->index }}'));
    }

    public function testLoopIterationVariableIsConverted(): void
    {
        $this->assertSame('{{ loop.index }}', $this->pattern->apply('{{ $loop->iteration }}'));
    }
}
