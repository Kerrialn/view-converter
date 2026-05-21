<?php

namespace ViewConverter\Printer\Blade\Pattern;

use ViewConverter\Printer\Contract\BladePatternInterface;
use ViewConverter\Util\BladeExpressionHelper;

final class LoopsPattern implements BladePatternInterface
{
    public function apply(string $content): string
    {
        // @foreach ($items as [$key =>] $value)
        $content = preg_replace_callback('/@foreach\s*\((.+?)\s+as\s+(.+?)\)/', fn($m) => BladeExpressionHelper::forStatement(trim($m[1]), trim($m[2])), $content);
        $content = str_replace('@endforeach', '{% endfor %}', $content);

        // Remaining @forelse without @empty (degenerate case)
        $content = preg_replace_callback('/@forelse\s*\((.+?)\s+as\s+(.+?)\)/', fn($m) => BladeExpressionHelper::forStatement(trim($m[1]), trim($m[2])), $content);
        $content = str_replace('@endforelse', '{% endfor %}', $content);

        // @for — no clean Twig equivalent
        $content = preg_replace_callback('/@for\s*\((.+?)\)/', fn($m) => '{# TODO: @for(' . trim($m[1]) . ') - review manually #}', $content);
        $content = str_replace('@endfor', '{% endfor %}', $content);

        // @while — no Twig equivalent
        $content = preg_replace_callback('/@while\s*\((.+?)\)/', fn($m) => '{# TODO: while (' . BladeExpressionHelper::convertExpr(trim($m[1])) . ') - no Twig equivalent #}', $content);
        $content = str_replace('@endwhile', '{# endwhile #}', $content);

        $content = preg_replace('/@continue(\(\d+\))?/', '{# @continue not supported in Twig #}', $content);
        $content = preg_replace('/@break(\(\d+\))?/', '{# @break not supported in Twig #}', $content);

        return $content;
    }
}
