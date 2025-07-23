<?php

namespace ViewConverter\Util;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\PrettyPrinter\Standard;
use ViewConverter\Parser\ParserHelper;
use ViewConverter\Printer\Contract\PrinterInterface;

final class ExpressionHelper
{
    /**
     * @param Name|Expr|null $expr
     * @param PrinterInterface $printer
     * @return string
     */
    public static function toString($expr, PrinterInterface $printer): string
    {
        if ($expr === null) {
            return 'null';
        }

        // is_null($x) → x is null
        if (
            $expr instanceof Expr\FuncCall &&
            ParserHelper::getCallableName($expr->name, fn($e) => $printer->exprToString($e)) === 'is_null' &&
            isset($expr->args[0])
        ) {
            return self::toString($expr->args[0]->value, $printer) . ' is null';
        }

        // !is_null($x) → x is not null
        if (
            $expr instanceof Expr\BooleanNot &&
            $expr->expr instanceof Expr\FuncCall &&
            ParserHelper::getCallableName($expr->expr->name, fn($e) => $printer->exprToString($e)) === 'is_null'
        ) {
            return self::toString($expr->expr->args[0]->value, $printer) . ' is not null';
        }

        // $foo['bar'] → foo.bar
        if ($expr instanceof Expr\ArrayDimFetch) {
            return self::arrayDimToDot($expr, $printer);
        }

        // $var → var
        if ($expr instanceof Expr\Variable && is_string($expr->name)) {
            return ltrim($expr->name, '$');
        }

        // $a + $b → a + b
        if ($expr instanceof Expr\BinaryOp) {
            return self::binaryOpToString($expr, $printer);
        }

        // $foo ? $bar : $baz
        if ($expr instanceof Expr\Ternary) {
            $cond = self::toString($expr->cond, $printer);
            $ifTrue = $expr->if !== null ? self::toString($expr->if, $printer) : 'null';
            $ifFalse = self::toString($expr->else, $printer);
            return "($cond ? $ifTrue : $ifFalse)";
        }

        // Function call
        if ($expr instanceof Expr\FuncCall) {
            $name = ParserHelper::getCallableName($expr->name, fn($e) => $printer->exprToString($e));
            $args = array_map(fn($arg) => self::toString($arg->value, $printer), $expr->args);
            return $name . '(' . implode(', ', $args) . ')';
        }

        // Fallback: use raw pretty printer
        $fallback = new Standard();
        return $fallback->prettyPrintExpr($expr);
    }

    private static function arrayDimToDot(Expr\ArrayDimFetch $expr, PrinterInterface $printer): string
    {
        $parts = [];

        while ($expr instanceof Expr\ArrayDimFetch) {
            $dim = $expr->dim;

            if ($dim instanceof Node\Scalar\String_) {
                $parts[] = $dim->value; // ✅ raw string, no quotes
            } elseif ($dim instanceof Expr) {
                $dimString = self::toString($dim, $printer);
                $parts[] = trim($dimString, '"\''); // remove quotes just in case
            } else {
                $parts[] = 'unknown';
            }

            $expr = $expr->var;
        }

        if ($expr instanceof Expr\Variable && is_string($expr->name)) {
            $parts[] = ltrim($expr->name, '$');
        }

        return implode('.', array_reverse($parts));
    }
    private static function binaryOpToString(Expr\BinaryOp $expr, PrinterInterface $printer): string
    {
        $left = self::toString($expr->left, $printer);
        $right = self::toString($expr->right, $printer);

        $class = get_class($expr);
        switch ($class) {
            case Expr\BinaryOp\Identical::class:
                $op = '===';
                break;
            case Expr\BinaryOp\NotIdentical::class:
                $op = '!==';
                break;
            case Expr\BinaryOp\Equal::class:
                $op = '==';
                break;
            case Expr\BinaryOp\NotEqual::class:
                $op = '!=';
                break;
            case Expr\BinaryOp\Smaller::class:
                $op = '<';
                break;
            case Expr\BinaryOp\SmallerOrEqual::class:
                $op = '<=';
                break;
            case Expr\BinaryOp\Greater::class:
                $op = '>';
                break;
            case Expr\BinaryOp\GreaterOrEqual::class:
                $op = '>=';
                break;
            case Expr\BinaryOp\Plus::class:
                $op = '+';
                break;
            case Expr\BinaryOp\Minus::class:
                $op = '-';
                break;
            case Expr\BinaryOp\Concat::class:
                $op = '~';
                break;
            case Expr\BinaryOp\BooleanAnd::class:
            case Expr\BinaryOp\LogicalAnd::class:
                $op = 'and';
                break;
            case Expr\BinaryOp\BooleanOr::class:
            case Expr\BinaryOp\LogicalOr::class:
                $op = 'or';
                break;
            default:
                $op = '??';
        }

        return "$left $op $right";
    }
}
