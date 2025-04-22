<?php

namespace Test\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpToTwig\Printer\Twig\Printer\FuncCallPrinter;
use PhpToTwig\Printer\Twig\Printer\ScalarPrinter;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class FuncCallPrinterTest extends TestCase
{
    public function testFunctionCallIsConvertedCorrectly()
    {
        $code = <<<PHP
<?php echo strtoupper('hello');
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new ScalarPrinter(),
            new EchoPrinter(),
            new FuncCallPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ strtoupper('hello') }}", trim($output));
    }

    public function testFunctionCallWithMultipleArgs()
    {
        $code = <<<PHP
<?php echo substr('hello', 1);
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new ScalarPrinter(),
            new EchoPrinter(),
            new FuncCallPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ substr('hello', 1) }}", trim($output));
    }
}
