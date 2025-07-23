<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\BooleanNotPrinter;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class BooleanNotPrinterTest extends TestCase
{
    public function testBooleanNotIsConvertedToNot()
    {
        $code = <<<PHP
<?php echo !\$foo;
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new VariablePrinter(),
            new BooleanNotPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ not foo }}", trim($output));
    }
}
