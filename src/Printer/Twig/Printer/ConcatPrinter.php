<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

final class ConcatPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Concat;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        return $printer->convertNode($node->left) . ' ~ ' . $printer->convertNode($node->right);
    }
}
