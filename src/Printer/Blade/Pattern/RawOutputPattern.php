<?php

namespace ViewConverter\Printer\Blade\Pattern;

use ViewConverter\Printer\Contract\BladePatternInterface;
use ViewConverter\Util\BladeExpressionHelper;

final class RawOutputPattern implements BladePatternInterface
{
    public function apply(string $content): string
    {
        return preg_replace_callback('/\{!!\s*(.+?)\s*!!\}/', function ($m) {
            return '{{ ' . BladeExpressionHelper::convertExpr($m[1]) . '|raw }}';
        }, $content);
    }
}
