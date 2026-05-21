<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Twig\Printer\InlineHtmlPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;

class InlineHtmlPrinterTest extends TestCase
{
    public function testInlineHtmlIsPrintedAsIs(): void
    {
        $code = <<<PHP
<?php ?>
<div class="box">Hello World</div>
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new InlineHtmlPrinter(),
        ]);

        $output = $printer->print($stmts);

        $expected = '<div class="box">Hello World</div>';

        $this->assertSame(trim($expected), trim($output));
    }
}
