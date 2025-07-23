<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\CastPrinter;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CastPrinterTest extends TestCase
{
    #[DataProvider('castProvider')]
    public function testCastsAreUnwrappedInTwig(string $code, string $expected): void
    {
        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse('<?php echo ' . $code . ';');

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new VariablePrinter(),
            new CastPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ $expected }}", trim($output));
    }

    public static function castProvider(): array
    {
        return [
            ['(object)$foo', 'foo'],
            ['(array)$foo', 'foo'],
            ['(int)$foo', 'foo'],
            ['(integer)$foo', 'foo'],
            ['(float)$foo', 'foo'],
            ['(double)$foo', 'foo'],
            ['(real)$foo', 'foo'],
            ['(bool)$foo', 'foo'],
            ['(boolean)$foo', 'foo'],
            ['(string)$foo', 'foo'],
            ['(unset)$foo', 'foo'],
        ];
    }
}
