<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class PropertyFetchPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof PropertyFetch;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        $parts = [];

        while ($node instanceof PropertyFetch) {
            $property = $node->name;
            $parts[] = $property instanceof Identifier
                ? $property->toString()
                : '{# unsupported property #}';
            $node = $node->var;
        }

        $base = $printer->convertNode($node);

        return $base . '.' . implode('.', array_reverse($parts));
    }
}