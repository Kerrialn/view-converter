<?php

namespace Test\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpToTwig\Printer\Twig\Printer\ArrayPrinter;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\Printer\ScalarPrinter;
use PhpToTwig\Printer\Twig\Printer\StringPrinter;
use PhpToTwig\Printer\Twig\TwigPrinter;
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
