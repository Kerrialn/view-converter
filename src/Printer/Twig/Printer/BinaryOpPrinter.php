<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

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

        $class = get_class($node);
        switch ($class) {
            case BinaryOp\Identical::class:
                $operator = '===';
                break;
            case BinaryOp\NotIdentical::class:
                $operator = '!==';
                break;
            case BinaryOp\Equal::class:
                $operator = '==';
                break;
            case BinaryOp\NotEqual::class:
                $operator = '!=';
                break;
            case BinaryOp\Smaller::class:
                $operator = '<';
                break;
            case BinaryOp\SmallerOrEqual::class:
                $operator = '<=';
                break;
            case BinaryOp\Greater::class:
                $operator = '>';
                break;
            case BinaryOp\GreaterOrEqual::class:
                $operator = '>=';
                break;
            case BinaryOp\BooleanAnd::class:
            case BinaryOp\LogicalAnd::class:
                $operator = 'and';
                break;
            case BinaryOp\BooleanOr::class:
            case BinaryOp\LogicalOr::class:
                $operator = 'or';
                break;
            case BinaryOp\Concat::class:
                $operator = '~';
                break;
            case BinaryOp\Plus::class:
                $operator = '+';
                break;
            case BinaryOp\Minus::class:
                $operator = '-';
                break;
            default:
                $operator = '??';
        }

        return "$left $operator $right";
    }

}
