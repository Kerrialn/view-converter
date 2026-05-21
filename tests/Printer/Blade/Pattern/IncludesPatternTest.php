<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use ViewConverter\Printer\Blade\Pattern\IncludesPattern;
use PHPUnit\Framework\TestCase;

class IncludesPatternTest extends TestCase
{
    private IncludesPattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new IncludesPattern();
    }

    public function testSimpleIncludeIsConverted(): void
    {
        $this->assertSame(
            "{% include 'partials/header.html.twig' %}",
            $this->pattern->apply("@include('partials.header')")
        );
    }

    public function testIncludeIfConvertsToIgnoreMissing(): void
    {
        $this->assertSame(
            "{% include 'partials/sidebar.html.twig' ignore missing %}",
            $this->pattern->apply("@includeIf('partials.sidebar')")
        );
    }

    public function testIncludeWhenConvertsToConditionalInclude(): void
    {
        $this->assertSame(
            "{% if user.isAdmin %}{% include 'partials/admin.html.twig' %}{% endif %}",
            $this->pattern->apply("@includeWhen(\$user->isAdmin, 'partials.admin')")
        );
    }

    public function testIncludeUnlessConvertsToNegatedConditional(): void
    {
        $this->assertSame(
            "{% if not (user.isGuest) %}{% include 'partials/nav.html.twig' %}{% endif %}",
            $this->pattern->apply("@includeUnless(\$user->isGuest, 'partials.nav')")
        );
    }
}
