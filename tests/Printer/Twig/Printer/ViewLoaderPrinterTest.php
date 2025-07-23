<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\ArrayPrinter;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\MethodCallPrinter;
use ViewConverter\Printer\Twig\Printer\PropertyFetchPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\Printer\StringPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\Printer\ViewLoaderPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class ViewLoaderPrinterTest extends TestCase
{
    public function testViewLoaderIsConvertedToTwigInclude()
    {
        $code = <<<PHP
<?php echo \$this->load->view('my/view', array('key' => 'value'));
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new ViewLoaderPrinter(),
            new MethodCallPrinter(),
            new PropertyFetchPrinter(),
            new ArrayPrinter(),
            new StringPrinter(),
            new ScalarPrinter(),
            new VariablePrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertStringContainsString("{{ include('my/view', { 'key': 'value' }) }}", $output);
    }
}