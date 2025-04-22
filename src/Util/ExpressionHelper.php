<?php

namespace PhpToTwig\Util;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\PrettyPrinter\Standard;
use PhpToTwig\Parser\ParserHelper;
use PhpToTwig\Printer\Contract\PrinterInterface;

final class ExpressionHelper
{
    public static function toString(Name|Expr|null $expr, PrinterInterface $printer): string
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

        $op = match (get_class($expr)) {
            Expr\BinaryOp\Identical::class => '===',
            Expr\BinaryOp\NotIdentical::class => '!==',
            Expr\BinaryOp\Equal::class => '==',
            Expr\BinaryOp\NotEqual::class => '!=',
            Expr\BinaryOp\Smaller::class => '<',
            Expr\BinaryOp\SmallerOrEqual::class => '<=',
            Expr\BinaryOp\Greater::class => '>',
            Expr\BinaryOp\GreaterOrEqual::class => '>=',
            Expr\BinaryOp\Plus::class => '+',
            Expr\BinaryOp\Minus::class => '-',
            Expr\BinaryOp\Concat::class => '~',
            Expr\BinaryOp\BooleanAnd::class,
            Expr\BinaryOp\LogicalAnd::class => 'and',
            Expr\BinaryOp\BooleanOr::class,
            Expr\BinaryOp\LogicalOr::class => 'or',
            default => '??',
        };

        return "$left $op $right";
    }
}
