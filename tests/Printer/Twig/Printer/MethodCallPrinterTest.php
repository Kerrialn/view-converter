<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\MethodCallPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\Printer\StringPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
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
