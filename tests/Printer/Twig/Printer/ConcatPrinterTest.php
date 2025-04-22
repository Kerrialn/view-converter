<?php

namespace Test\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpToTwig\Printer\Twig\Printer\ConcatPrinter;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\Printer\ScalarPrinter;
use PhpToTwig\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class ConcatPrinterTest extends TestCase
{
    public function testConcatIsConvertedToTildeOperator()
    {
        $code = <<<PHP
<?php echo 'Hello' . 'World';
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new ScalarPrinter(),
            new ConcatPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ 'Hello' ~ 'World' }}", trim($output));
    }
}
