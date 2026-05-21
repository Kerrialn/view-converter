<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\Printer\WhilePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;

class WhilePrinterTest extends TestCase
{
    public function testWhileOutputsTodoComment(): void
    {
        $code = <<<PHP
<?php
while (\$running) {
    echo 'tick';
}
PHP;
        $output = $this->print($code);

        $this->assertStringContainsString('{# TODO: while (running)', $output);
        $this->assertStringContainsString("{# endwhile #}", $output);
    }

    public function testWhileBodyIsIncluded(): void
    {
        $code = <<<PHP
<?php
while (\$active) {
    echo 'item';
}
PHP;
        $output = $this->print($code);

        $this->assertStringContainsString("{{ 'item' }}", $output);
    }

    /**
     * @return \PhpParser\Node\Stmt[]
     */
    private function parse(string $code): array
    {
        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        return $parser->parse($code);
    }

    private function print(string $code): string
    {
        $printer = new TwigPrinter([
            new VariablePrinter(),
            new EchoPrinter(),
            new ScalarPrinter(),
            new WhilePrinter(),
        ]);
        return $printer->print($this->parse($code));
    }
}
