<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\ArrayPrinter;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\Printer\StringPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class ArrayPrinterTest extends TestCase
{
    public function testArrayIsConvertedToTwigObject()
    {
        $code = <<<PHP
<?php echo array('foo' => 'bar', 'baz' => 42);
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new ArrayPrinter(),
            new ScalarPrinter(),
            new StringPrinter(),
        ]);


        $output = $printer->print($stmts);

        $this->assertStringContainsString("{ 'foo': 'bar', 'baz': 42 }", $output);
    }
}
