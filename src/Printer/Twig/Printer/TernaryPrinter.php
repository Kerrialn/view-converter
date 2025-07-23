<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class TernaryPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Ternary;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        $cond = $printer->convertNode($node->cond);
        $if = $node->if !== null ? $printer->convertNode($node->if) : 'null';
        $else = $printer->convertNode($node->else);
        return "$cond ? $if : $else";
    }
}
