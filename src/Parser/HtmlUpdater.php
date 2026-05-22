<?php

namespace ViewConverter\Parser;

class HtmlUpdater
{
    /**
     * Applies suggested data-attribute changes to template files in place.
     *
     * Groups suggestions by file and line, merges any duplicate data-action
     * descriptors, then injects all attributes before the closing > of each
     * matched opening tag.
     *
     * @param array<int, array{file: string, line: int, attribute: string}> $suggestions
     * @return string[] List of modified file paths
     */
    public function update(array $suggestions): array
    {
        $byFile = [];
        foreach ($suggestions as $s) {
            $byFile[$s['file']][$s['line']][] = $s['attribute'];
        }

        $modified = [];

        foreach ($byFile as $filePath => $lineMap) {
            if ($this->updateFile($filePath, $lineMap)) {
                $modified[] = $filePath;
            }
        }

        return $modified;
    }

    /**
     * @param array<int, string[]> $lineMap  line number (1-based) → attribute strings
     */
    private function updateFile(string $filePath, array $lineMap): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $lines = explode("\n", $content);
        $changed = false;

        foreach ($lineMap as $lineNum => $attributes) {
            $index = $lineNum - 1;
            if (!array_key_exists($index, $lines)) {
                continue;
            }

            $merged = $this->mergeActionAttributes($attributes);
            $updated = $this->injectAttributes($lines[$index], $merged);

            if ($updated !== $lines[$index]) {
                $lines[$index] = $updated;
                $changed = true;
            }
        }

        if ($changed) {
            file_put_contents($filePath, implode("\n", $lines));
        }

        return $changed;
    }

    /**
     * Collapses multiple data-action attributes into one with space-separated descriptors.
     *
     * @param string[] $attributes
     * @return string[]
     */
    private function mergeActionAttributes(array $attributes): array
    {
        $actionDescriptors = [];
        $other = [];

        foreach ($attributes as $attr) {
            if (preg_match('/^data-action="([^"]*)"$/', $attr, $m)) {
                $actionDescriptors[] = $m[1];
            } else {
                $other[] = $attr;
            }
        }

        if ($actionDescriptors !== []) {
            $other[] = 'data-action="' . implode(' ', $actionDescriptors) . '"';
        }

        return $other;
    }

    /**
     * Injects attribute strings before the closing > of the first opening tag on the line.
     * Uses a simple state machine so > characters inside quoted attribute values are ignored.
     *
     * @param string[] $attributes
     */
    private function injectAttributes(string $line, array $attributes): string
    {
        $len = strlen($line);
        $inTag = false;
        $inQuote = false;
        $quoteChar = '';
        $insertPos = -1;
        $isSelfClosing = false;

        for ($i = 0; $i < $len; $i++) {
            $c = $line[$i];

            if (!$inTag) {
                if ($c === '<' && ($i + 1) < $len && ctype_alpha($line[$i + 1])) {
                    $inTag = true;
                }
                continue;
            }

            if ($inQuote) {
                if ($c === $quoteChar) {
                    $inQuote = false;
                }
                continue;
            }

            if ($c === '"' || $c === "'") {
                $inQuote = true;
                $quoteChar = $c;
                continue;
            }

            if ($c === '>') {
                if ($i > 0 && $line[$i - 1] === '/') {
                    $insertPos = $i - 1;
                    $isSelfClosing = true;
                } else {
                    $insertPos = $i;
                }
                break;
            }
        }

        if ($insertPos === -1) {
            return $line;
        }

        $attrString = ' ' . implode(' ', $attributes);
        $before = rtrim(substr($line, 0, $insertPos));
        $after = $isSelfClosing
            ? ' />' . substr($line, $insertPos + 2)
            : '>' . substr($line, $insertPos + 1);

        return $before . $attrString . $after;
    }
}
