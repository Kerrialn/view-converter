<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\StringPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class EchoPrinterTest extends TestCase
{
    public function testEchoStatementPrintsAsTwigOutput()
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
