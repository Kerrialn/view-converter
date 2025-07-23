<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class VariablePrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Variable;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        return $node->name;
    }

}
