<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\Empty_;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class EmptyCheckPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Empty_;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var Empty_ $node */
        return $printer->exprToString($node->expr) . ' is empty';
    }
}
