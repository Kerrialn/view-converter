<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class StaticCallPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof StaticCall;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var StaticCall $node */
        $class = $node->class instanceof Name
            ? $node->class->toString()
            : $printer->convertNode($node->class);

        $method = $node->name instanceof Identifier
            ? $node->name->toString()
            : $printer->convertNode($node->name);

        $args = array_map(fn($arg) => $printer->convertNode($arg->value), $node->args);

        return "{# static: $class::$method(" . implode(', ', $args) . ") #}";
    }
}
