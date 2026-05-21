<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\ForPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class ForPrinterTest extends TestCase
{
    public function testSimpleExclusiveForConvertsToRange()
    {
        $code = <<<PHP
<?php
for (\$i = 0; \$i < 5; \$i++) {
    echo \$i;
}
PHP;
        $output = $this->print($code);

        $this->assertStringContainsString('{% for i in range(0, 4) %}', $output);
        $this->assertStringContainsString('{{ i }}', $output);
        $this->assertStringContainsString('{% endfor %}', $output);
    }

    public function testSimpleInclusiveForConvertsToRange()
    {
        $code = <<<PHP
<?php
for (\$i = 1; \$i <= 3; \$i++) {
    echo \$i;
}
PHP;
        $output = $this->print($code);

        $this->assertStringContainsString('{% for i in range(1, 3) %}', $output);
        $this->assertStringContainsString('{% endfor %}', $output);
    }

    public function testComplexForFallsBackToComment()
    {
        $code = <<<PHP
<?php
for (\$i = 0, \$j = 0; \$i < 10; \$i++, \$j++) {
    echo \$i;
}
PHP;
        $output = $this->print($code);

        $this->assertStringContainsString('{# TODO: for (', $output);
    }

    private function parse(string $code): array
    {
        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        return $parser->parse($code);
    }

    private function print(string $code): string
    {
        $printer = new TwigPrinter([
            new VariablePrinter(),
            new EchoPrinter(),
            new ScalarPrinter(),
            new ForPrinter(),
        ]);
        return $printer->print($this->parse($code));
    }
}
