<?php

namespace ViewConverter\Printer\Blade\Pattern;

use ViewConverter\Printer\Contract\BladePatternInterface;

final class CommentsPattern implements BladePatternInterface
{
    public function apply(string $content): string
    {
        return preg_replace('/\{\{--(.+?)--\}\}/s', '{#$1#}', $content);
    }
}
