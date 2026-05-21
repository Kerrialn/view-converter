<?php

namespace ViewConverter\Printer\Blade\Pattern;

use ViewConverter\Printer\Contract\BladePatternInterface;
use ViewConverter\Util\BladeExpressionHelper;

final class ConditionalsPattern implements BladePatternInterface
{
    public function apply(string $content): string
    {
        // @isset($var) → {% if var is defined %}
        $content = preg_replace_callback('/@isset\s*\((.+?)\)/', function ($m) {
            return '{% if ' . BladeExpressionHelper::convertExpr(trim($m[1])) . ' is defined %}';
        }, $content);
        $content = str_replace('@endisset', '{% endif %}', $content);

        // @empty($var) → {% if var is empty %}
        $content = preg_replace_callback('/@empty\s*\((.+?)\)/', function ($m) {
            return '{% if ' . BladeExpressionHelper::convertExpr(trim($m[1])) . ' is empty %}';
        }, $content);
        $content = str_replace('@endempty', '{% endif %}', $content);

        // @unless($cond) → {% if not (cond) %}
        $content = preg_replace_callback('/@unless\s*\((.+?)\)/', function ($m) {
            return '{% if not (' . BladeExpressionHelper::convertExpr(trim($m[1])) . ') %}';
        }, $content);
        $content = str_replace('@endunless', '{% endif %}', $content);

        // @auth / @guest
        $content = preg_replace('/@auth\b/', '{% if app.user %}', $content);
        $content = str_replace('@endauth', '{% endif %}', $content);
        $content = preg_replace('/@guest\b/', '{% if not app.user %}', $content);
        $content = str_replace('@endguest', '{% endif %}', $content);

        // @if / @elseif / @else / @endif
        $content = preg_replace_callback('/@if\s*\((.+?)\)/', function ($m) {
            return '{% if ' . BladeExpressionHelper::convertExpr(trim($m[1])) . ' %}';
        }, $content);
        $content = preg_replace_callback('/@elseif\s*\((.+?)\)/', function ($m) {
            return '{% elseif ' . BladeExpressionHelper::convertExpr(trim($m[1])) . ' %}';
        }, $content);
        $content = preg_replace('/@else\b/', '{% else %}', $content);
        $content = str_replace('@endif', '{% endif %}', $content);

        return $content;
    }
}
