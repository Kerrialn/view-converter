<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\PropertyFetchPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class PropertyFetchPrinterTest extends TestCase
{
    public function testSimplePropertyFetchIsConvertedCorrectly()
    {
        $code = <<<PHP
<?php echo \$eventData->title;
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new VariablePrinter(),
            new PropertyFetchPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ eventData.title }}", trim($output));
    }

    public function testNestedPropertyFetchIsConvertedCorrectly()
    {
        $code = <<<PHP
<?php echo \$user->profile->email;
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new EchoPrinter(),
            new VariablePrinter(),
            new PropertyFetchPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertSame("{{ user.profile.email }}", trim($output));
    }
}
