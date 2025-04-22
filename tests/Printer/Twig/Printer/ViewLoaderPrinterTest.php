<?php

namespace Test\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpToTwig\Printer\Twig\Printer\ArrayPrinter;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\Printer\MethodCallPrinter;
use PhpToTwig\Printer\Twig\Printer\PropertyFetchPrinter;
use PhpToTwig\Printer\Twig\Printer\ScalarPrinter;
use PhpToTwig\Printer\Twig\Printer\StringPrinter;
use PhpToTwig\Printer\Twig\Printer\VariablePrinter;
use PhpToTwig\Printer\Twig\Printer\ViewLoaderPrinter;
use PhpToTwig\Printer\Twig\TwigPrinter;
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