<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\Isset_;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class IssetPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Isset_;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var Isset_ $node */
        $checks = array_map(
            fn($var) => $printer->exprToString($var) . ' is defined',
            $node->vars
        );

        return implode(' and ', $checks);
    }
}
