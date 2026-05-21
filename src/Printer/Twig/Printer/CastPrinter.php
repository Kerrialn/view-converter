<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class CastPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Node\Expr\Cast;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        if (! $node instanceof Node\Expr\Cast) {
            return '';
        }

        return $printer->convertNode($node->expr);
    }
}
