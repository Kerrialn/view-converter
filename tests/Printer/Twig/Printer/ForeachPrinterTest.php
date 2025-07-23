<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\ForeachPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
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
