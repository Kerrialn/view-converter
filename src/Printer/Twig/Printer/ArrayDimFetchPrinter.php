<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class ArrayDimFetchPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof ArrayDimFetch;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        $parts = [];

        while ($node instanceof ArrayDimFetch) {
            $dim = $node->dim;

            // Use convertNode and strip quotes if needed
            $dimStr = $printer->convertNode($dim);
            $dimStr = trim($dimStr, '"\''); // ensure no surrounding quotes

            $parts[] = $dimStr;
            $node = $node->var;
        }

        // Should resolve to something like eventData
        $base = $printer->convertNode($node);

        return $base . '.' . implode('.', array_reverse($parts));
    }
}
