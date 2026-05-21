<?php

namespace ViewConverter\Util;

final class BladeExpressionHelper
{
    /**
     * @var array<string, string>
     */
    private static array $loopMap = [
        '$loop->iteration' => 'loop.index',
        '$loop->index' => 'loop.index0',
        '$loop->remaining' => '(loop.length - loop.index)',
        '$loop->count' => 'loop.length',
        '$loop->first' => 'loop.first',
        '$loop->last' => 'loop.last',
        '$loop->depth' => 'loop.depth',
        '$loop->parent' => 'loop.parent',
    ];

    public static function convertExpr(string $expr): string
    {
        $expr = trim($expr);

        // Strip outer parentheses wrapping the whole expression
        if (preg_match('/^\((.+)\)$/', $expr, $m)) {
            $expr = $m[1];
        }

        // $loop variables — must come before general property conversion
        $expr = str_replace(array_keys(self::$loopMap), array_values(self::$loopMap), $expr);

        // count($var) → var|length
        $expr = preg_replace_callback('/\bcount\s*\(\s*\$([a-zA-Z_]\w*)\s*\)/', fn($m) => $m[1] . '|length', $expr);

        // PHP comparison/logical operators
        $expr = str_replace(['===', '!=='], ['==', '!='], $expr);
        $expr = str_replace(['&&', '||'], [' and ', ' or '], $expr);
        $expr = preg_replace('/!(?!=)/', 'not ', $expr);

        // Array access: $var['key'] or $var["key"] → var.key
        $expr = preg_replace_callback('/\$([a-zA-Z_]\w*)\[\'([^\']+)\'\]/', fn($m) => $m[1] . '.' . $m[2], $expr);
        $expr = preg_replace_callback('/\$([a-zA-Z_]\w*)\["([^"]+)"\]/', fn($m) => $m[1] . '.' . $m[2], $expr);

        // Property/method chain: $obj->prop->sub, $obj->method()
        $expr = preg_replace_callback(
            '/\$([a-zA-Z_]\w*)((?:->(?:[a-zA-Z_]\w*)(?:\([^)]*\))?)+ )/',
            function ($m) {
                $chain = preg_replace('/->/', '.', ltrim($m[2], '->'));
                return $m[1] . '.' . trim($chain);
            },
            $expr
        );
        $expr = preg_replace_callback(
            '/\$([a-zA-Z_]\w*)((?:->(?:[a-zA-Z_]\w*)(?:\([^)]*\))?)+)/',
            function ($m) {
                $chain = preg_replace('/->/', '.', ltrim($m[2], '->'));
                return $m[1] . '.' . $chain;
            },
            $expr
        );

        // Strip $ from remaining bare variables
        $expr = preg_replace('/\$([a-zA-Z_]\w*)/', '$1', $expr);

        return preg_replace('/\s{2,}/', ' ', trim($expr));
    }

    public static function convertPath(string $path): string
    {
        if (strpos($path, '/') !== false || strpos($path, '.twig') !== false) {
            return $path;
        }
        return str_replace('.', '/', $path) . '.html.twig';
    }

    public static function convertArrayLiteral(string $phpArray): string
    {
        $phpArray = trim($phpArray);
        $phpArray = preg_replace('/^\[/', '{', $phpArray);
        $phpArray = preg_replace('/\]$/', '}', $phpArray);
        $phpArray = preg_replace('/[\'"]([^\'"]+)[\'"]\s*=>/', '$1:', $phpArray);
        return $phpArray;
    }

    public static function forStatement(string $iterable, string $vars): string
    {
        $iterable = self::convertExpr(trim($iterable));
        $vars = trim($vars);

        if (strpos($vars, '=>') !== false) {
            [$key, $value] = explode('=>', $vars, 2);
            $key = ltrim(trim($key), '$');
            $value = ltrim(trim($value), '$');
            return "{% for $key, $value in $iterable %}";
        }

        $value = ltrim($vars, '$');
        return "{% for $value in $iterable %}";
    }
}
