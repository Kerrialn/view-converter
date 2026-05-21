<?php

namespace ViewConverter\Converter;

use ViewConverter\Printer\Blade\BladePrinter;

final class BladeConverter
{
    public function convert(string $content): string
    {
        return (new BladePrinter())->convert($content);
    }
}
