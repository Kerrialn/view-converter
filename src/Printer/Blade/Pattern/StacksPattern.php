<?php

namespace ViewConverter\Printer\Blade\Pattern;

use ViewConverter\Printer\Contract\BladePatternInterface;

final class StacksPattern implements BladePatternInterface
{
    public function apply(string $content): string
    {
        $content = preg_replace_callback('/@push\([\'"](.+?)[\'"]\)/', function ($m) {
            return '{% block ' . str_replace(['-', '.'], '_', $m[1]) . ' %}';
        }, $content);
        $content = str_replace('@endpush', '{% endblock %}', $content);

        $content = preg_replace_callback('/@stack\([\'"](.+?)[\'"]\)/', function ($m) {
            return "{{ block('" . $m[1] . "') }}";
        }, $content);

        return $content;
    }
}
