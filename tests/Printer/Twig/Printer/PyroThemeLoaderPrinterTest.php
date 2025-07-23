<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\MethodCallPrinter;
use ViewConverter\Printer\Twig\Printer\PropertyFetchPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\Printer\StringPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\Printer\PyroThemeLoaderPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
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
