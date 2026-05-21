<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;

class ScalarPrinterTest extends TestCase
{
    /**
     * @dataProvider scalarProvider
     */
    public function testScalarValuesArePrintedCorrectly(string $phpExpr, string $expectedTwigOutput): void
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

    /**
     * @return array<string, array{string, string}>
     */
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
