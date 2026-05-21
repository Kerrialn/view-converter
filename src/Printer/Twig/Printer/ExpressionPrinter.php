<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

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

        // Include/require → {% include %}
        if ($expr instanceof Expr\Include_) {
            $path = $printer->exprToString($expr->expr);
            return "{% include $path %}";
        }

        // Static call → delegate to StaticCallPrinter (returns a comment)
        if ($expr instanceof Expr\StaticCall) {
            return $printer->convertNode($expr);
        }

        // Method call or function call → {{ expr }}
        if ($expr instanceof Expr\MethodCall || $expr instanceof Expr\FuncCall) {
            return '{{ ' . $printer->exprToString($expr) . ' }}';
        }

        // Fallback: wrap as output value
        $converted = $printer->exprToString($expr);
        return '{{ ' . $converted . ' }}';
    }
}
