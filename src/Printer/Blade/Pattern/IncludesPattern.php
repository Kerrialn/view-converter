<?php

namespace ViewConverter\Printer\Blade\Pattern;

use ViewConverter\Printer\Contract\BladePatternInterface;
use ViewConverter\Util\BladeExpressionHelper;

final class IncludesPattern implements BladePatternInterface
{
    public function apply(string $content): string
    {
        // @includeWhen($cond, 'partial') → {% if cond %}{% include 'partial' %}{% endif %}
        $content = preg_replace_callback('/@includeWhen\((.+?),\s*[\'"](.+?)[\'"]\)/', function ($m) {
            $cond = BladeExpressionHelper::convertExpr(trim($m[1]));
            $path = BladeExpressionHelper::convertPath($m[2]);
            return "{% if $cond %}{% include '$path' %}{% endif %}";
        }, $content);

        // @includeUnless($cond, 'partial') → {% if not (cond) %}{% include %}{% endif %}
        $content = preg_replace_callback('/@includeUnless\((.+?),\s*[\'"](.+?)[\'"]\)/', function ($m) {
            $cond = BladeExpressionHelper::convertExpr(trim($m[1]));
            $path = BladeExpressionHelper::convertPath($m[2]);
            return "{% if not ($cond) %}{% include '$path' %}{% endif %}";
        }, $content);

        // @includeIf('partial') → {% include 'partial' ignore missing %}
        $content = preg_replace_callback('/@includeIf\([\'"](.+?)[\'"]\)/', fn($m) => "{% include '" . BladeExpressionHelper::convertPath($m[1]) . "' ignore missing %}", $content);

        // @include('partial', ['key' => 'val']) → {% include 'partial' with {key: 'val'} %}
        $content = preg_replace_callback('/@include\([\'"](.+?)[\'"]\s*,\s*(\[.+?\])\)/s', function ($m) {
            $path = BladeExpressionHelper::convertPath($m[1]);
            $data = BladeExpressionHelper::convertArrayLiteral($m[2]);
            return "{% include '$path' with $data %}";
        }, $content);

        // @include('partial') → {% include 'partial' %}
        $content = preg_replace_callback('/@include\([\'"](.+?)[\'"]\)/', fn($m) => "{% include '" . BladeExpressionHelper::convertPath($m[1]) . "' %}", $content);

        return $content;
    }
}
