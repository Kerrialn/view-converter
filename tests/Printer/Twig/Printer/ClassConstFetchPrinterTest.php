<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\Printer\ClassConstFetchPrinter;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;
use PHPUnit\Framework\TestCase;

class ClassConstFetchPrinterTest extends TestCase
{
    public function testClassConstantConvertsToTwigConstantFunction()
    {
        $code = <<<PHP
<?php echo App::VERSION;
PHP;
        $output = $this->print($code);

        $this->assertSame("{{ constant('App::VERSION') }}", trim($output));
    }

    public function testNamespacedClassConstantIsConverted()
    {
        $code = <<<PHP
<?php echo MyApp\Config::DEBUG;
PHP;
        $output = $this->print($code);

        $this->assertStringContainsString("constant('", $output);
        $this->assertStringContainsString('::DEBUG', $output);
    }

    private function parse(string $code): array
    {
        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.4'));
        return $parser->parse($code);
    }

    private function print(string $code): string
    {
        $printer = new TwigPrinter([
            new ClassConstFetchPrinter(),
            new EchoPrinter(),
        ]);
        return $printer->print($this->parse($code));
    }
}
