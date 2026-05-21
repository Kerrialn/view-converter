<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\EmptyCheckPrinter;
use ViewConverter\Printer\Twig\Printer\IfPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class EmptyCheckPrinterTest extends TestCase
{
    public function testEmptyConvertsToIsEmptyCheck()
    {
        $code = <<<PHP
<?php if (empty(\$items)) { echo 'none'; }
PHP;
        $output = $this->print($code);

        $this->assertStringContainsString('{% if items is empty %}', $output);
    }

    private function parse(string $code): array
    {
        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        return $parser->parse($code);
    }

    private function print(string $code): string
    {
        $printer = new TwigPrinter([
            new VariablePrinter(),
            new ScalarPrinter(),
            new EchoPrinter(),
            new EmptyCheckPrinter(),
            new IfPrinter(),
        ]);
        return $printer->print($this->parse($code));
    }
}
