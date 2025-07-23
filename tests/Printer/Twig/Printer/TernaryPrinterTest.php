<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\ConstFetchPrinter;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\Printer\TernaryPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TernaryPrinterTest extends TestCase
{
    #[DataProvider('ternaryProvider')]
    public function testTernaryIsConvertedCorrectly(string $phpExpr, string $expectedTwigOutput): void
    {
        $code = <<<PHP
<?php echo $phpExpr;
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new ScalarPrinter(),
            new TernaryPrinter(),
            new VariablePrinter(),
            new ConstFetchPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ $expectedTwigOutput }}", trim($output));
    }

    public static function ternaryProvider(): array
    {
        return [
            'full ternary' => ['true ? "yes" : "no"', 'true ? \'yes\' : \'no\''],
            'short ternary (elvis)' => ['$foo ?: "default"', 'foo ? null : \'default\''],
        ];
    }
}
