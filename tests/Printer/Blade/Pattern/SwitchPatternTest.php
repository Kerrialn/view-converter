<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use ViewConverter\Printer\Blade\Pattern\SwitchPattern;
use PHPUnit\Framework\TestCase;

class SwitchPatternTest extends TestCase
{
    private SwitchPattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new SwitchPattern();
    }

    public function testSwitchConvertsToIfElseifChain(): void
    {
        $input = <<<BLADE
@switch(\$status)
    @case('active')
        Active
        @break
    @case('inactive')
        Inactive
        @break
    @default
        Unknown
@endswitch
BLADE;

        $output = $this->pattern->apply($input);

        $this->assertStringContainsString("{% if status == 'active' %}", $output);
        $this->assertStringContainsString("{% elseif status == 'inactive' %}", $output);
        $this->assertStringContainsString('{% else %}', $output);
        $this->assertStringContainsString('{% endif %}', $output);
        $this->assertStringContainsString('Active', $output);
        $this->assertStringContainsString('Unknown', $output);
    }
}
