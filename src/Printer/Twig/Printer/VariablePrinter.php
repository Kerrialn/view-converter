<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

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
