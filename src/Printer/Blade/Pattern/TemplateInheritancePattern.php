<?php

namespace ViewConverter\Printer\Blade\Pattern;

use ViewConverter\Printer\Contract\BladePatternInterface;
use ViewConverter\Util\BladeExpressionHelper;

final class TemplateInheritancePattern implements BladePatternInterface
{
    public function apply(string $content): string
    {
        // @extends('layout') → {% extends 'layout.html.twig' %}
        $content = preg_replace_callback('/@extends\([\'"](.+?)[\'"]\)/', fn($m) => "{% extends '" . BladeExpressionHelper::convertPath($m[1]) . "' %}", $content);

        // @section('name', 'inline-value') → {% block name %}'value'{% endblock %}
        $content = preg_replace_callback('/@section\([\'"](.+?)[\'"]\s*,\s*[\'"](.+?)[\'"]\)/', fn($m) => "{% block {$m[1]} %}'{$m[2]}'{% endblock %}", $content);

        // @section('name') → {% block name %}
        $content = preg_replace_callback('/@section\([\'"](.+?)[\'"]\)/', fn($m) => '{% block ' . $m[1] . ' %}', $content);

        $content = preg_replace('/@endsection\b|@stop\b|@show\b/', '{% endblock %}', $content);

        // @yield('name', 'default') → {% block name %}'default'{% endblock %}
        $content = preg_replace_callback('/@yield\([\'"](.+?)[\'"]\s*,\s*[\'"](.+?)[\'"]\)/', fn($m) => "{% block {$m[1]} %}'{$m[2]}'{% endblock %}", $content);

        // @yield('name') → {% block name %}{% endblock %}
        $content = preg_replace_callback('/@yield\([\'"](.+?)[\'"]\)/', fn($m) => '{% block ' . $m[1] . ' %}{% endblock %}', $content);

        $content = str_replace('@parent', '{{ parent() }}', $content);

        return $content;
    }
}
