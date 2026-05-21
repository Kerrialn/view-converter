<?php

namespace ViewConverter\Printer\Blade\Pattern;

use ViewConverter\Printer\Contract\BladePatternInterface;
use ViewConverter\Util\BladeExpressionHelper;

final class ForeachElsePattern implements BladePatternInterface
{
    public function apply(string $content): string
    {
        // Handle the full @forelse...@empty...@endforelse block as a unit
        return preg_replace_callback(
            '/@forelse\s*\((.+?)\s+as\s+(.+?)\)(.*?)@empty(.*?)@endforelse/s',
            function ($m) {
                $header = BladeExpressionHelper::forStatement(trim($m[1]), trim($m[2]));
                return $header . $m[3] . "{% else %}" . $m[4] . "{% endfor %}";
            },
            $content
        );
    }
}
