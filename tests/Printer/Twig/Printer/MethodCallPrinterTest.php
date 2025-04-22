<?php

namespace Test\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\Printer\MethodCallPrinter;
use PhpToTwig\Printer\Twig\Printer\VariablePrinter;
use PhpToTwig\Printer\Twig\Printer\StringPrinter;
use PhpToTwig\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class MethodCallPrinterTest extends TestCase
{
    public function testSimpleMethodCallIsConvertedCorrectly()
    {
        $code = <<<PHP
<?php echo \$user->getName();
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new VariablePrinter(),
            new MethodCallPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ user.getName() }}", trim($output));
    }

    public function testMethodCallWithArgumentsIsConvertedCorrectly()
    {
        $code = <<<PHP
<?php echo \$user->greet('friend');
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new VariablePrinter(),
            new MethodCallPrinter(),
            new StringPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ user.greet('friend') }}", trim($output));
    }
}
