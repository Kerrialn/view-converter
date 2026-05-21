<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Blade\Pattern\ConditionalsPattern;

class ConditionalsPatternTest extends TestCase
{
    private ConditionalsPattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new ConditionalsPattern();
    }

    public function testIfConvertsToTwigIf(): void
    {
        $this->assertSame('{% if active %}', $this->pattern->apply('@if($active)'));
    }

    public function testElseifConvertsToTwigElseif(): void
    {
        $this->assertSame('{% elseif pending %}', $this->pattern->apply('@elseif($pending)'));
    }

    public function testElseConvertsToTwigElse(): void
    {
        $this->assertSame('{% else %}', $this->pattern->apply('@else'));
    }

    public function testEndifConvertsToTwigEndif(): void
    {
        $this->assertSame('{% endif %}', $this->pattern->apply('@endif'));
    }

    public function testIssetConvertsToIsDefinedCheck(): void
    {
        $this->assertSame('{% if user is defined %}', $this->pattern->apply('@isset($user)'));
    }

    public function testEndissetConvertsToEndif(): void
    {
        $this->assertSame('{% endif %}', $this->pattern->apply('@endisset'));
    }

    public function testEmptyConvertsToIsEmptyCheck(): void
    {
        $this->assertSame('{% if items is empty %}', $this->pattern->apply('@empty($items)'));
    }

    public function testUnlessConvertsToNegatedIf(): void
    {
        $this->assertSame('{% if not (admin) %}', $this->pattern->apply('@unless($admin)'));
    }

    public function testEndunlessConvertsToEndif(): void
    {
        $this->assertSame('{% endif %}', $this->pattern->apply('@endunless'));
    }

    public function testAuthConvertsToUserCheck(): void
    {
        $this->assertSame('{% if app.user %}', $this->pattern->apply('@auth'));
    }

    public function testGuestConvertsToNegatedUserCheck(): void
    {
        $this->assertSame('{% if not app.user %}', $this->pattern->apply('@guest'));
    }
}
