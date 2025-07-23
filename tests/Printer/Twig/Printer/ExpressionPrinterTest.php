<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\ExpressionPrinter;
use ViewConverter\Printer\Twig\Printer\FuncCallPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class ExpressionPrinterTest extends TestCase
{
    public function testAssignmentIsConvertedToSet()
    {
        $code = <<<PHP
<?php \$foo = 'bar';
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new VariablePrinter(),
            new ScalarPrinter(),
            new ExpressionPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame('{% set foo = \'bar\' %}', trim($output));
    }

    public function testFunctionCallIsWrappedWithDoubleCurlyBraces()
    {
        $code = <<<PHP
<?php foo();
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new FuncCallPrinter(),
            new ExpressionPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame('{{ foo() }}', trim($output));
    }
}
