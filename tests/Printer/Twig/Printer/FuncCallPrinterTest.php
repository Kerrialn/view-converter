<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\FuncCallPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;

class FuncCallPrinterTest extends TestCase
{
    public function testFunctionCallIsConvertedCorrectly(): void
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

    public function testFunctionCallWithMultipleArgs(): void
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
