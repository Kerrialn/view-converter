<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Blade\Pattern\RawOutputPattern;

class RawOutputPatternTest extends TestCase
{
    private RawOutputPattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new RawOutputPattern();
    }

    public function testRawVariableGetsRawFilter(): void
    {
        $this->assertSame('{{ html|raw }}', $this->pattern->apply('{!! $html !!}'));
    }

    public function testPropertyChainInRawOutput(): void
    {
        $this->assertSame('{{ user.bio|raw }}', $this->pattern->apply('{!! $user->bio !!}'));
    }
}
