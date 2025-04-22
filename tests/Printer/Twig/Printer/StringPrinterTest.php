<?php

namespace Test\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\Printer\StringPrinter;
use PhpToTwig\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class StringPrinterTest extends TestCase
{
    public function testSingleQuotedStringIsPrintedWithoutModification()
    {
        $code = <<<PHP
        <?php echo 'hello';
        PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new StringPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ 'hello' }}", trim($output));
    }

}
