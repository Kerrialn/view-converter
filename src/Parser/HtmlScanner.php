<?php

namespace ViewConverter\Parser;

use Symfony\Component\Finder\Finder;
use ViewConverter\Stimulus\StimulusController;

class HtmlScanner
{
    private const EXTENSIONS = ['*.html', '*.php', '*.twig', '*.blade.php', '*.html.twig'];

    /**
     * Scans a directory for template files and returns suggested Stimulus attribute changes.
     *
     * @return array<int, array{file: string, line: int, original: string, suggestion: string, attribute: string, type: string}>
     */
    public function scan(string $directory, StimulusController $controller): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $finder = new Finder();
        $finder->in($directory)->files();
        foreach (self::EXTENSIONS as $ext) {
            $finder->name($ext);
        }

        $suggestions = [];

        foreach ($finder as $file) {
            $fileSuggestions = $this->scanFile($file->getPathname(), $controller);
            $suggestions = array_merge($suggestions, $fileSuggestions);
        }

        return $suggestions;
    }

    /**
     * @return array<int, array{file: string, line: int, original: string, suggestion: string, attribute: string, type: string}>
     */
    private function scanFile(string $filePath, StimulusController $controller): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        $suggestions = [];
        $lines = explode("\n", $content);
        $controllerName = $controller->getName();

        // Build a map: CSS selector → { type, dataAttributes }
        $selectorMap = $this->buildSelectorMap($controller);

        foreach ($lines as $lineNumber => $lineContent) {
            foreach ($selectorMap as $selector => $meta) {
                if (! $this->lineMatchesSelector($lineContent, $selector)) {
                    continue;
                }

                foreach ($meta as $info) {
                    $suggestions[] = [
                        'file' => $filePath,
                        'line' => $lineNumber + 1,
                        'original' => trim($lineContent),
                        'suggestion' => $info['suggestion'],
                        'attribute' => $info['attribute'],
                        'type' => $info['type'],
                    ];
                }
            }
        }

        return $suggestions;
    }

    /**
     * Builds a map from CSS selector strings to suggested data attributes.
     *
     * @return array<string, array<int, array{suggestion: string, attribute: string, type: string}>>
     */
    private function buildSelectorMap(StimulusController $controller): array
    {
        $map = [];
        $name = $controller->getName();

        // Root selector → data-controller
        if ($controller->getRootSelector() !== null) {
            $selector = $controller->getRootSelector();
            $attr = 'data-controller="' . $name . '"';
            $map[$selector][] = [
                'suggestion' => 'Add ' . $attr,
                'attribute' => $attr,
                'type' => 'controller',
            ];
        }

        // Targets → data-[name]-target
        foreach ($controller->getTargets() as $target) {
            if ($target->getSelector() === null) {
                continue;
            }
            $selector = $target->getSelector();
            $attr = 'data-' . $name . '-target="' . $target->getName() . '"';
            $map[$selector][] = [
                'suggestion' => 'Add ' . $attr,
                'attribute' => $attr,
                'type' => 'target',
            ];
        }

        // Actions → data-action on the target element
        foreach ($controller->getActions() as $action) {
            $targetName = $action->getTargetName();
            $target = $this->findTarget($controller, $targetName);
            if ($target === null || $target->getSelector() === null) {
                continue;
            }
            $selector = $target->getSelector();
            $descriptor = $action->getEvent() . '->' . $name . '#' . $action->getMethodName();
            $attr = 'data-action="' . $descriptor . '"';
            $map[$selector][] = [
                'suggestion' => 'Add ' . $attr,
                'attribute' => $attr,
                'type' => 'action',
            ];
        }

        return $map;
    }

    private function findTarget(StimulusController $controller, string $name): ?\ViewConverter\Stimulus\StimulusTarget
    {
        foreach ($controller->getTargets() as $target) {
            if ($target->getName() === $name) {
                return $target;
            }
        }
        return null;
    }

    /**
     * Checks if an HTML line contains an element matching the given CSS selector.
     * Supports: .class-name and #id selectors.
     */
    private function lineMatchesSelector(string $line, string $selector): bool
    {
        if ($selector === '') {
            return false;
        }

        if ($selector[0] === '#') {
            $id = substr($selector, 1);
            return (bool) preg_match('/\bid=["\']' . preg_quote($id, '/') . '["\']/', $line);
        }

        if ($selector[0] === '.') {
            $class = substr($selector, 1);
            // Extract the class attribute value, then check for an exact token match.
            // \b does not work here because CSS class names use hyphens (non-word chars).
            if (preg_match('/\bclass=["\']([^"\']*)["\']/', $line, $matches)) {
                $tokens = ' ' . $matches[1] . ' ';
                return (bool) preg_match('/ ' . preg_quote($class, '/') . ' /', $tokens);
            }
            return false;
        }

        // Bare element selector (e.g. "button") — match opening tag
        return (bool) preg_match('/<' . preg_quote($selector, '/') . '[\s>]/', $line);
    }
}
