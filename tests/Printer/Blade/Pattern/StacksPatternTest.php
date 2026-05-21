<?php

namespace ViewConverterTest\Printer\Blade\Pattern;

use PHPUnit\Framework\TestCase;
use ViewConverter\Printer\Blade\Pattern\StacksPattern;

class StacksPatternTest extends TestCase
{
    private StacksPattern $pattern;

    protected function setUp(): void
    {
        $this->pattern = new StacksPattern();
    }

    public function testPushConvertsToBlock(): void
    {
        $this->assertSame('{% block scripts %}', $this->pattern->apply("@push('scripts')"));
    }

    public function testEndpushConvertsToEndblock(): void
    {
        $this->assertSame('{% endblock %}', $this->pattern->apply('@endpush'));
    }

    public function testStackConvertsToBlockCall(): void
    {
        $this->assertSame("{{ block('scripts') }}", $this->pattern->apply("@stack('scripts')"));
    }

    public function testPushWithHyphensNormalisesName(): void
    {
        $this->assertSame('{% block page_scripts %}', $this->pattern->apply("@push('page-scripts')"));
    }
}
