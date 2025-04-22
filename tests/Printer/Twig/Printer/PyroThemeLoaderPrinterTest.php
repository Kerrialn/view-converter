<?php

namespace Test\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\Printer\MethodCallPrinter;
use PhpToTwig\Printer\Twig\Printer\PropertyFetchPrinter;
use PhpToTwig\Printer\Twig\Printer\ScalarPrinter;
use PhpToTwig\Printer\Twig\Printer\StringPrinter;
use PhpToTwig\Printer\Twig\Printer\VariablePrinter;
use PhpToTwig\Printer\Twig\Printer\PyroThemeLoaderPrinter;
use PhpToTwig\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class PyroThemeLoaderPrinterTest extends TestCase
{
    public function testThemePartialIsConvertedToTodoComment()
    {
        $code = <<<PHP
<?php echo \$this->theme->partial('nb_notices');
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new PyroThemeLoaderPrinter(),
            new MethodCallPrinter(),
            new PropertyFetchPrinter(),
            new VariablePrinter(),
            new StringPrinter(),
            new ScalarPrinter(),
        ]);

        $output = $printer->print($stmts);


        $this->assertStringContainsString("TODO: replace theme with include", $output);
    }
}
