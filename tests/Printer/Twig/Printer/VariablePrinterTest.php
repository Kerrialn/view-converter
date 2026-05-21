<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;

class VariablePrinterTest extends TestCase
{
    public function testVariableIsPrintedCorrectly(): void
    {
        $code = <<<PHP
<?php echo \$foo;
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new VariablePrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ foo }}", trim($output));
    }
}
