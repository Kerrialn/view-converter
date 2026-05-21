<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use ViewConverter\Printer\Blade\Pattern\ForeachElsePattern;
use PHPUnit\Framework\TestCase;

class ForeachElsePatternTest extends TestCase
{
    private ForeachElsePattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new ForeachElsePattern();
    }

    public function testForelseConvertsToForWithElse(): void
    {
        $input = <<<BLADE
@forelse(\$users as \$user)
    <p>{{ \$user->name }}</p>
@empty
    <p>No users found.</p>
@endforelse
BLADE;

        $output = $this->pattern->apply($input);

        $this->assertStringContainsString('{% for user in users %}', $output);
        $this->assertStringContainsString('{% else %}', $output);
        $this->assertStringContainsString('{% endfor %}', $output);
        $this->assertStringContainsString('No users found.', $output);
    }

    public function testKeyValueForeachElseIsConverted(): void
    {
        $input = "@forelse(\$items as \$key => \$value)\n    x\n@empty\n    none\n@endforelse";
        $output = $this->pattern->apply($input);

        $this->assertStringContainsString('{% for key, value in items %}', $output);
        $this->assertStringContainsString('{% else %}', $output);
    }
}
