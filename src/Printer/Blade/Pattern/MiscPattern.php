<?php

namespace ViewConverter\Printer\Blade\Pattern;

use ViewConverter\Printer\Contract\BladePatternInterface;
use ViewConverter\Util\BladeExpressionHelper;

final class MiscPattern implements BladePatternInterface
{
    public function apply(string $content): string
    {
        $content = str_replace(['@verbatim', '@endverbatim'], ['{% verbatim %}', '{% endverbatim %}'], $content);

        $content = preg_replace_callback('/@json\s*\((.+?)\)/', function ($m) {
            return '{{ ' . BladeExpressionHelper::convertExpr(trim($m[1])) . '|json_encode }}';
        }, $content);

        $content = str_replace('@csrf', '{# @csrf - handle via form_tag or meta tag #}', $content);
        $content = preg_replace('/@method\([\'"][A-Z]+[\'"]\)/', '{# @method - handle via _method field #}', $content);

        return $content;
    }
}
