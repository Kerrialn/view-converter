<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Blade\Pattern\TemplateInheritancePattern;

class TemplateInheritancePatternTest extends TestCase
{
    private TemplateInheritancePattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new TemplateInheritancePattern();
    }

    public function testExtendsConvertsToTwigExtends(): void
    {
        $this->assertSame(
            "{% extends 'layouts/app.html.twig' %}",
            $this->pattern->apply("@extends('layouts.app')")
        );
    }

    public function testSectionBlockConvertsToTwigBlock(): void
    {
        $this->assertSame('{% block content %}', $this->pattern->apply("@section('content')"));
    }

    public function testEndsectionConvertsToEndblock(): void
    {
        $this->assertSame('{% endblock %}', $this->pattern->apply('@endsection'));
    }

    public function testStopConvertsToEndblock(): void
    {
        $this->assertSame('{% endblock %}', $this->pattern->apply('@stop'));
    }

    public function testInlineSectionConvertsToBlock(): void
    {
        $this->assertSame(
            "{% block title %}'My App'{% endblock %}",
            $this->pattern->apply("@section('title', 'My App')")
        );
    }

    public function testYieldConvertsToBlockPlaceholder(): void
    {
        $this->assertSame(
            '{% block content %}{% endblock %}',
            $this->pattern->apply("@yield('content')")
        );
    }

    public function testYieldWithDefaultConvertsToBlockWithDefault(): void
    {
        $this->assertSame(
            "{% block title %}'Default Title'{% endblock %}",
            $this->pattern->apply("@yield('title', 'Default Title')")
        );
    }

    public function testParentConvertsToParentCall(): void
    {
        $this->assertSame('{{ parent() }}', $this->pattern->apply('@parent'));
    }
}
