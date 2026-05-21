<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\For_;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class ForPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof For_;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var For_ $node */
        $simple = $this->trySimpleRange($node, $printer);
        if ($simple !== null) {
            return $simple;
        }

        $cond = count($node->cond) > 0 ? $printer->exprToString($node->cond[0]) : 'true';
        $output = "{# TODO: for ($cond) - review manually #}";
        foreach ($node->stmts as $stmt) {
            $output .= "\n" . $printer->convertNode($stmt);
        }
        $output .= "\n{# endfor #}";

        return $output;
    }

    private function trySimpleRange(For_ $node, PrinterInterface $printer): ?string
    {
        if (count($node->init) !== 1 || count($node->cond) !== 1 || count($node->loop) !== 1) {
            return null;
        }

        $init = $node->init[0];
        $cond = $node->cond[0];
        $loop = $node->loop[0];

        if (!$init instanceof Assign || !$init->var instanceof Variable) {
            return null;
        }

        $varName = is_string($init->var->name) ? $init->var->name : null;
        if ($varName === null) {
            return null;
        }

        $start = $printer->exprToString($init->expr);

        $exclusive = false;
        $endExpr = null;
        if ($cond instanceof BinaryOp\Smaller && $cond->left instanceof Variable && $cond->left->name === $varName) {
            $exclusive = true;
            $endExpr = $cond->right;
        } elseif ($cond instanceof BinaryOp\SmallerOrEqual && $cond->left instanceof Variable && $cond->left->name === $varName) {
            $endExpr = $cond->right;
        } else {
            return null;
        }

        $isIncrement = ($loop instanceof PostInc || $loop instanceof PreInc)
            && $loop->var instanceof Variable
            && $loop->var->name === $varName;

        if (!$isIncrement) {
            return null;
        }

        if ($exclusive) {
            $end = $endExpr instanceof Int_
                ? (string) ($endExpr->value - 1)
                : '(' . $printer->exprToString($endExpr) . ') - 1';
        } else {
            $end = $printer->exprToString($endExpr);
        }

        $output = "{% for $varName in range($start, $end) %}";
        foreach ($node->stmts as $stmt) {
            $output .= "\n" . $printer->convertNode($stmt);
        }
        $output .= "\n{% endfor %}";

        return $output;
    }
}
