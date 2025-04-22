<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Else_;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

final class IfPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof If_;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var If_ $node */
        $output = "{% if " . $printer->exprToString($node->cond) . " %}";

        foreach ($node->stmts as $stmt) {
            $output .= "\n" . $printer->convertNode($stmt);
        }

        foreach ($node->elseifs as $elseif) {
            /** @var ElseIf_ $elseif */
            $output .= "\n{% elseif " . $printer->exprToString($elseif->cond) . " %}";
            foreach ($elseif->stmts as $stmt) {
                $output .= "\n" . $printer->convertNode($stmt);
            }
        }

        if ($node->else instanceof Else_) {
            $output .= "\n{% else %}";
            foreach ($node->else->stmts as $stmt) {
                $output .= "\n" . $printer->convertNode($stmt);
            }
        }

        $output .= "\n{% endif %}";

        return $output;
    }
}
