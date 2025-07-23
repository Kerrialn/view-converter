<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScalarPrinterTest extends TestCase
{
    #[DataProvider('scalarProvider')]
    public function testScalarValuesArePrintedCorrectly(string $phpExpr, string $expectedTwigOutput)
    {
        $code = <<<PHP
<?php echo $phpExpr;
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new ScalarPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ $expectedTwigOutput }}", trim($output));
    }

    public static function scalarProvider(): array
    {
        return [
            'single-quoted string' => ["'hello'", "'hello'"],
            'double-quoted string' => ['"world"', "'world'"], // still becomes single-quoted
            'integer' => ['42', '42'],
            'float' => ['3.14', '3.14'],
        ];
    }
}
