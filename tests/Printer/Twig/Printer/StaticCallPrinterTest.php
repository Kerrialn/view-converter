<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Twig\Printer\ExpressionPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\Printer\StaticCallPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;

class StaticCallPrinterTest extends TestCase
{
    public function testStaticCallConvertsToComment(): void
    {
        $code = <<<PHP
<?php \$x = Foo::bar('baz');
PHP;
        $output = $this->print($code);

        $this->assertStringContainsString('{# static: Foo::bar(', $output);
    }

    public function testStaticCallWithNoArgsConvertsToComment(): void
    {
        $code = <<<PHP
<?php \$x = Helper::getInstance();
PHP;
        $output = $this->print($code);

        $this->assertStringContainsString('{# static: Helper::getInstance() #}', $output);
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
            new ScalarPrinter(),
            new StaticCallPrinter(),
            new ExpressionPrinter(),
        ]);
        return $printer->print($this->parse($code));
    }
}
