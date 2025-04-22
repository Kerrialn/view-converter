<?php

namespace Test\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\Printer\ExpressionPrinter;
use PhpToTwig\Printer\Twig\Printer\FuncCallPrinter;
use PhpToTwig\Printer\Twig\Printer\ScalarPrinter;
use PhpToTwig\Printer\Twig\Printer\VariablePrinter;
use PhpToTwig\Printer\Twig\TwigPrinter;
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
