<?php

namespace Test\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpToTwig\Printer\Twig\Printer\BinaryOpPrinter;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\Printer\ScalarPrinter;
use PhpToTwig\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BinaryOpPrinterTest extends TestCase
{
    #[DataProvider('binaryOpProvider')]
    public function testBinaryOpIsConvertedCorrectly(string $expression, string $expectedTwig): void
    {
        $code = <<<PHP
<?php echo $expression;
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new ScalarPrinter(),
            new BinaryOpPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ $expectedTwig }}", trim($output));
    }

    /**
     * @return string[]
     */
    public static function binaryOpProvider(): array
    {
        return [
            ['1 + 2', '1 + 2'],
            ['1 - 2', '1 - 2'],
            ['1 . 2', '1 ~ 2'],
            ['1 == 2', '1 == 2'],
            ['1 === 2', '1 === 2'],
            ['1 != 2', '1 != 2'],
            ['1 !== 2', '1 !== 2'],
            ['1 < 2', '1 < 2'],
            ['1 <= 2', '1 <= 2'],
            ['1 > 2', '1 > 2'],
            ['1 >= 2', '1 >= 2'],
            ['1 && 2', '1 and 2'],
            ['1 and 2', '1 and 2'],
            ['1 || 2', '1 or 2'],
            ['1 or 2', '1 or 2'],
        ];
    }
}
