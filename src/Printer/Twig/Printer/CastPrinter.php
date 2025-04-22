<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Object_;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

final class CastPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Node\Expr\Cast;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        return $printer->convertNode($node->expr);
    }
}
