<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class ViewLoaderPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        // Must be a method call like ->view()
        if (! $node instanceof MethodCall) {
            return false;
        }

        // Check that the method being called is `view`
        if (! $node->name instanceof Identifier || $node->name->toString() !== 'view') {
            return false;
        }

        // The method call should be on something like `$this->load`
        $onLoad = $node->var;
        if (! $onLoad instanceof PropertyFetch) {
            return false;
        }

        // `$this->load`
        if (
            $onLoad->var instanceof Variable &&
            $onLoad->var->name === 'this' &&
            $onLoad->name instanceof Identifier &&
            $onLoad->name->toString() === 'load'
        ) {
            return true;
        }

        return false;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        $viewPath = $printer->convertNode($node->args[0]->value);
        $context = isset($node->args[1]) ? $printer->convertNode($node->args[1]->value) : '{}';

        return "include($viewPath, $context)";
    }
}