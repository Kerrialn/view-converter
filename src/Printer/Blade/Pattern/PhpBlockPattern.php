<?php

namespace ViewConverter\Printer\Blade\Pattern;

use ViewConverter\Printer\Contract\BladePatternInterface;

final class PhpBlockPattern implements BladePatternInterface
{
    public function apply(string $content): string
    {
        return preg_replace('/@php(.+?)@endphp/s', '{# PHP block - review manually #}', $content);
    }
}
