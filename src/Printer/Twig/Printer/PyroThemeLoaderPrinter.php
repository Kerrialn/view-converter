<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

final class PyroThemeLoaderPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        // Check that the method being called is `partial`
        if (! $node->name instanceof Identifier || $node->name->toString() !== 'partial') {
            return false;
        }

        // Check if it's $this->theme->partial()
        $caller = $node->var;
        if (! $caller instanceof PropertyFetch) {
            return false;
        }

        // Check that it's `$this->theme`
        if (
            $caller->var instanceof Variable &&
            $caller->var->name === 'this' &&
            $caller->name instanceof Identifier &&
            $caller->name->toString() === 'theme'
        ) {
            return true;
        }

        return false;
    }
    public function print(Node $node, PrinterInterface $printer): string
    {
        $args = array_map(fn($arg) => $printer->convertNode($arg->value), $node->args);
        return "{# TODO: replace theme with include #}\n{{ theme:partial name=" . $args[0] . " }}";
    }

}