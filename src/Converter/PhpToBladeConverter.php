<?php

namespace ViewConverter\Converter;

class PhpToBladeConverter
{
    // Negative lookahead prevents the match from crossing a PHP closing tag boundary
    private const INNER = '(?:(?!\?>)[\s\S])';

    public function convert(string $content): string
    {
        $content = $this->convertEchoTags($content);
        $content = $this->convertControlStructures($content);
        $content = $this->wrapRemainingPhpBlocks($content);
        return $content;
    }

    private function convertEchoTags(string $content): string
    {
        $inner = self::INNER;

        // short-echo and long-echo tags, semicolon optional
        $content = preg_replace(
            "/\<\?=\s*({$inner}*?)\s*\?>/",
            '{{ $1 }}',
            $content
        );

        $content = preg_replace(
            "/\<\?php\s+echo\s+({$inner}*?);?\s*\?>/",
            '{{ $1 }}',
            $content
        );

        return $content;
    }

    private function convertControlStructures(string $content): string
    {
        $inner = self::INNER;

        $replacements = [
            "/\<\?php\s+if\s*\(({$inner}*?)\)\s*:\s*\?>/" => '@if($1)',
            "/\<\?php\s+elseif\s*\(({$inner}*?)\)\s*:\s*\?>/" => '@elseif($1)',
            '/\<\?php\s+else\s*:\s*\?>/' => '@else',
            '/\<\?php\s+endif\s*;?\s*\?>/' => '@endif',
            "/\<\?php\s+foreach\s*\(({$inner}*?)\)\s*:\s*\?>/" => '@foreach($1)',
            '/\<\?php\s+endforeach\s*;?\s*\?>/' => '@endforeach',
            "/\<\?php\s+for\s*\(({$inner}*?)\)\s*:\s*\?>/" => '@for($1)',
            '/\<\?php\s+endfor\s*;?\s*\?>/' => '@endfor',
            "/\<\?php\s+while\s*\(({$inner}*?)\)\s*:\s*\?>/" => '@while($1)',
            '/\<\?php\s+endwhile\s*;?\s*\?>/' => '@endwhile',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }

    private function wrapRemainingPhpBlocks(string $content): string
    {
        $inner = self::INNER;
        return preg_replace(
            "/\<\?php\s+({$inner}*?)\s*\?>/",
            "@php\n$1\n@endphp",
            $content
        );
    }
}
