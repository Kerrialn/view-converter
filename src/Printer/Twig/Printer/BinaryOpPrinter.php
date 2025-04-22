<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

final class BinaryOpPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof BinaryOp;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        $left = $printer->convertNode($node->left);
        $right = $printer->convertNode($node->right);
        $operator = match (get_class($node)) {
            BinaryOp\Identical::class => '===',
            BinaryOp\NotIdentical::class => '!==',
            BinaryOp\Equal::class => '==',
            BinaryOp\NotEqual::class => '!=',
            BinaryOp\Smaller::class => '<',
            BinaryOp\SmallerOrEqual::class => '<=',
            BinaryOp\Greater::class => '>',
            BinaryOp\GreaterOrEqual::class => '>=',
            BinaryOp\BooleanAnd::class,
            BinaryOp\LogicalAnd::class => 'and',
            BinaryOp\BooleanOr::class,
            BinaryOp\LogicalOr::class => 'or',
            BinaryOp\Concat::class => '~',
            BinaryOp\Plus::class => '+',
            BinaryOp\Minus::class => '-',
            default => '??'
        };

        return "$left $operator $right";
    }
}
