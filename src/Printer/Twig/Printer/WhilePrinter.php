<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Stmt\While_;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class WhilePrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof While_;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var While_ $node */
        $cond = $printer->exprToString($node->cond);
        $output = "{# TODO: while ($cond) - no Twig equivalent, review manually #}";

        foreach ($node->stmts as $stmt) {
            $output .= "\n" . $printer->convertNode($stmt);
        }

        $output .= "\n{# endwhile #}";

        return $output;
    }
}
