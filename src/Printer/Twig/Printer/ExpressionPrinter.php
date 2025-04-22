<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

final class ExpressionPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Expression;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var Expression $node */
        $expr = $node->expr;

        // Assignment → {% set var = value %}
        if ($expr instanceof Expr\Assign) {
            $var = $printer->exprToString($expr->var);
            $value = $printer->exprToString($expr->expr);
            return "{% set $var = $value %}";
        }

        // Standalone function call → {{ someFunc(...) }}
        if ($expr instanceof Expr\FuncCall) {
            return '{{ ' . $printer->exprToString($expr) . ' }}';
        }

        // Static method call? Method call? Raw expression? — fallback
        return '{# unsupported expression: ' . get_class($expr) . ' #}';
    }
}
