<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Twig\Printer\BinaryOpPrinter;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\IfPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;

class IfPrinterTest extends TestCase
{
    public function testSimpleIfIsConverted(): void
    {
        $code = <<<PHP
<?php if (true) { echo 'yes'; }
PHP;

        $printer = $this->createPrinter();
        $stmts = $this->parse($code);

        $expected = <<<TWIG
{% if true %}
{{ 'yes' }}
{% endif %}
TWIG;

        $this->assertSame(trim($expected), trim($printer->print($stmts)));
    }

    public function testIfElseIsConverted(): void
    {
        $code = <<<PHP
<?php if (true) { echo 'yes'; } else { echo 'no'; }
PHP;

        $printer = $this->createPrinter();
        $stmts = $this->parse($code);

        $expected = <<<TWIG
{% if true %}
{{ 'yes' }}
{% else %}
{{ 'no' }}
{% endif %}
TWIG;

        $this->assertSame(trim($expected), trim($printer->print($stmts)));
    }

    public function testIfElseIfIsConverted(): void
    {
        $code = <<<PHP
<?php if (false) { echo 'no'; } elseif (true) { echo 'maybe'; }
PHP;

        $printer = $this->createPrinter();
        $stmts = $this->parse($code);

        $expected = <<<TWIG
{% if false %}
{{ 'no' }}
{% elseif true %}
{{ 'maybe' }}
{% endif %}
TWIG;

        $this->assertSame(trim($expected), trim($printer->print($stmts)));
    }

    public function testFullIfElseIfElse(): void
    {
        $code = <<<PHP
<?php if (false) { echo 'no'; } elseif (false) { echo 'maybe'; } else { echo 'yes'; }
PHP;

        $printer = $this->createPrinter();
        $stmts = $this->parse($code);

        $expected = <<<TWIG
{% if false %}
{{ 'no' }}
{% elseif false %}
{{ 'maybe' }}
{% else %}
{{ 'yes' }}
{% endif %}
TWIG;

        $this->assertSame(trim($expected), trim($printer->print($stmts)));
    }

    /**
     * @return \PhpParser\Node\Stmt[]
     */
    private function parse(string $code): array
    {
        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        return $parser->parse($code);
    }

    private function createPrinter(): TwigPrinter
    {
        return new TwigPrinter([
            new ScalarPrinter(),
            new EchoPrinter(),
            new BinaryOpPrinter(),
            new IfPrinter(),
        ]);
    }
}
