<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\ArrayDimFetchPrinter;
use ViewConverter\Printer\Twig\Printer\BinaryOpPrinter;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\FuncCallPrinter;
use ViewConverter\Printer\Twig\Printer\IfPrinter;
use ViewConverter\Printer\Twig\Printer\StringPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class ArrayDimFetchPrinterTest extends TestCase
{
    public function testArrayDimFetchIsConvertedToTwigDotSyntax()
    {
        $code = <<<PHP
<?php
if (trim(\$eventData['title']) != "") {
    echo "yes";
}
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new StringPrinter(),
            new EchoPrinter(),
            new IfPrinter(),
            new FuncCallPrinter(),
            new BinaryOpPrinter(),
            new ArrayDimFetchPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertStringContainsString("eventData.title", $output);
    }

    public function testArrayAccessIsPrinted()
    {
        $code = <<<PHP
<?php echo \$eventData['title'];
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new VariablePrinter(),
            new StringPrinter(),
            new EchoPrinter(),
            new IfPrinter(),
            new FuncCallPrinter(),
            new BinaryOpPrinter(),
            new ArrayDimFetchPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertStringContainsString("eventData.title", $output);
    }

}
