<?php

namespace Test\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\Printer\ForeachPrinter;
use PhpToTwig\Printer\Twig\Printer\ScalarPrinter;
use PhpToTwig\Printer\Twig\Printer\VariablePrinter;
use PhpToTwig\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class ForeachPrinterTest extends TestCase
{
    public function testBasicForeachIsConvertedToTwigSyntax()
    {
        $code = <<<PHP
<?php
foreach (\$items as \$item) {
    echo \$item;
}
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new VariablePrinter(),
            new EchoPrinter(),
            new ForeachPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertStringContainsString('{% for item in items %}', $output);
        $this->assertStringContainsString('{{ item }}', $output);
        $this->assertStringContainsString('{% endfor %}', $output);
    }

    public function testKeyValueForeachIsConvertedToTwigSyntax()
    {
        $code = <<<PHP
<?php
foreach (\$items as \$key => \$value) {
    echo \$value;
}
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new VariablePrinter(),
            new EchoPrinter(),
            new ForeachPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertStringContainsString('{% for key, value in items %}', $output);
        $this->assertStringContainsString('{{ value }}', $output);
        $this->assertStringContainsString('{% endfor %}', $output);
    }
}
