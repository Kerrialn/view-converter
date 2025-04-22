<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

final class BooleanNotPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof BooleanNot;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        return 'not ' . $printer->convertNode($node->expr);
    }
}
