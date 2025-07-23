<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\ConstFetchPrinter;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConstFetchPrinterTest extends TestCase
{
    #[DataProvider('constFetchProvider')]
    public function testConstFetchIsPrintedCorrectly(string $phpCode, string $expectedTwig): void
    {
        $code = <<<PHP
<?php echo $phpCode;
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new ConstFetchPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ $expectedTwig }}", trim($output));
    }

    public static function constFetchProvider(): array
    {
        return [
            'true' => ['true', 'true'],
            'false' => ['false', 'false'],
            'null' => ['null', 'null'],
            'TRUE (case-insensitive)' => ['TRUE', 'true'],
            'FALSE (case-insensitive)' => ['FALSE', 'false'],
        ];
    }
}
