<?php

namespace ViewConverter\Printer\Blade;

use ViewConverter\Printer\Blade\Pattern\CommentsPattern;
use ViewConverter\Printer\Blade\Pattern\ConditionalsPattern;
use ViewConverter\Printer\Blade\Pattern\EscapedOutputPattern;
use ViewConverter\Printer\Blade\Pattern\ForeachElsePattern;
use ViewConverter\Printer\Blade\Pattern\IncludesPattern;
use ViewConverter\Printer\Blade\Pattern\LoopsPattern;
use ViewConverter\Printer\Blade\Pattern\MiscPattern;
use ViewConverter\Printer\Blade\Pattern\PhpBlockPattern;
use ViewConverter\Printer\Blade\Pattern\RawOutputPattern;
use ViewConverter\Printer\Blade\Pattern\StacksPattern;
use ViewConverter\Printer\Blade\Pattern\SwitchPattern;
use ViewConverter\Printer\Blade\Pattern\TemplateInheritancePattern;
use ViewConverter\Printer\Contract\BladePatternInterface;

final class BladePrinter
{
    /** @var BladePatternInterface[] */
    private array $patterns;

    public function __construct(array $patterns = [])
    {
        $this->patterns = $patterns ?: self::defaultPatterns();
    }

    public function convert(string $content): string
    {
        foreach ($this->patterns as $pattern) {
            $content = $pattern->apply($content);
        }
        return $content;
    }

    public static function defaultPatterns(): array
    {
        return [
            new CommentsPattern(),
            new RawOutputPattern(),
            new PhpBlockPattern(),
            new TemplateInheritancePattern(),
            new IncludesPattern(),
            new SwitchPattern(),
            new ForeachElsePattern(),
            new LoopsPattern(),
            new ConditionalsPattern(),
            new StacksPattern(),
            new MiscPattern(),
            new EscapedOutputPattern(),
        ];
    }
}
