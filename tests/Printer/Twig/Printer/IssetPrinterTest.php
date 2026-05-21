<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\IfPrinter;
use ViewConverter\Printer\Twig\Printer\IssetPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class IssetPrinterTest extends TestCase
{
    public function testIssetConvertsToIsDefinedCheck()
    {
        $code = <<<PHP
<?php if (isset(\$user)) { echo 'hi'; }
PHP;
        $output = $this->print($code);

        $this->assertStringContainsString('{% if user is defined %}', $output);
    }

    public function testMultipleIssetConvertsToAndChain()
    {
        $code = <<<PHP
<?php if (isset(\$a, \$b)) { echo 'ok'; }
PHP;
        $output = $this->print($code);

        $this->assertStringContainsString('a is defined and b is defined', $output);
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
            new IssetPrinter(),
            new IfPrinter(),
        ]);
        return $printer->print($this->parse($code));
    }
}
