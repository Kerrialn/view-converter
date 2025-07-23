<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class MethodCallPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof MethodCall;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var MethodCall $node */
        $object = $printer->convertNode($node->var);
        $methodName = $node->name instanceof Identifier ? $node->name->toString() : $printer->convertNode($node->name);
        $args = array_map(fn($arg) => $printer->convertNode($arg->value), $node->args);

        return "$object.$methodName(" . implode(', ', $args) . ")";
    }
}
