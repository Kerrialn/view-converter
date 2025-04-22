<?php

namespace Test\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\Printer\VariablePrinter;
use PhpToTwig\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

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
