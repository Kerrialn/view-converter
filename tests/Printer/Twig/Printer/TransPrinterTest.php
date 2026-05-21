<?php

namespace ViewConverterTest\Printer\Twig\Printer;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\TransPrinter;
use ViewConverter\Printer\Twig\TwigPrinter;

class TransPrinterTest extends TestCase
{
    public function testTranslateLangChainConvertsToTrans(): void
    {
        $code = <<<PHP
<?php echo translate(lang('bbevents:club_events:btn:return_to_event_page'));
PHP;

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.0'));
        $stmts = $parser->parse($code);

        $printer = new TwigPrinter([
            new TransPrinter(),
            new EchoPrinter(),
        ]);

        $output = $printer->print($stmts);

        $this->assertStringContainsString("{{ trans('bbevents.club_events.btn.return_to_event_page') }}", $output);
    }
}
