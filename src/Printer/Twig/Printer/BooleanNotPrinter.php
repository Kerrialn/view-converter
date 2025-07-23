<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

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
