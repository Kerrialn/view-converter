<?php

namespace ViewConverter\Util;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\PrettyPrinter\Standard;
use ViewConverter\Parser\ParserHelper;
use ViewConverter\Printer\Contract\PrinterInterface;

final class ExpressionHelper
{
    /**
     * @param Name|Expr|null $expr
     */
    public static function toString($expr, PrinterInterface $printer): string
    {
        if ($expr === null) {
            return 'null';
        }

        // isset($x) → x is defined, isset($x, $y) → x is defined and y is defined
        if ($expr instanceof Expr\Isset_) {
            $checks = array_map(
                fn($var) => self::toString($var, $printer) . ' is defined',
                $expr->vars
            );
            return implode(' and ', $checks);
        }

        // empty($x) → x is empty
        if ($expr instanceof Expr\Empty_) {
            return self::toString($expr->expr, $printer) . ' is empty';
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

        // Scalar literals
        if ($expr instanceof Node\Scalar\String_) {
            return "'" . addslashes($expr->value) . "'";
        }
        if ($expr instanceof Node\Scalar\Int_ || $expr instanceof Node\Scalar\Float_) {
            return (string) $expr->value;
        }
        if ($expr instanceof Node\Scalar\Encapsed) {
            $parts = [];
            foreach ($expr->parts as $part) {
                if ($part instanceof Node\Scalar\EncapsedStringPart) {
                    if ($part->value !== '') {
                        $parts[] = "'" . addslashes($part->value) . "'";
                    }
                } else {
                    $parts[] = self::toString($part, $printer);
                }
            }
            return empty($parts) ? "''" : implode(' ~ ', $parts);
        }

        // Boolean/null constants
        if ($expr instanceof Node\Expr\ConstFetch) {
            return strtolower((string) $expr->name);
        }

        // $obj->prop → obj.prop
        if ($expr instanceof Expr\PropertyFetch) {
            return self::propertyFetchToString($expr, $printer);
        }

        // $obj->method(args) → obj.method(args)
        if ($expr instanceof Expr\MethodCall) {
            $object = self::toString($expr->var, $printer);
            $method = $expr->name instanceof Identifier ? $expr->name->toString() : self::toString($expr->name, $printer);
            $args = array_map(fn($arg) => self::toString($arg->value, $printer), $expr->args);
            return "$object.$method(" . implode(', ', $args) . ")";
        }

        // ClassName::CONST → constant('ClassName::CONST')
        if ($expr instanceof Expr\ClassConstFetch) {
            $class = $expr->class instanceof Name ? $expr->class->toString() : self::toString($expr->class, $printer);
            $const = $expr->name instanceof Identifier ? $expr->name->toString() : '';
            return "constant('$class::$const')";
        }

        // ClassName::method() → static call comment
        if ($expr instanceof Expr\StaticCall) {
            $class = $expr->class instanceof Name ? $expr->class->toString() : self::toString($expr->class, $printer);
            $method = $expr->name instanceof Identifier ? $expr->name->toString() : '';
            $args = array_map(fn($arg) => self::toString($arg->value, $printer), $expr->args);
            return "{# static: $class::$method(" . implode(', ', $args) . ") #}";
        }

        // $a + $b → a + b (and other binary ops)
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

        // include/require → include(path)
        if ($expr instanceof Expr\Include_) {
            return 'include(' . self::toString($expr->expr, $printer) . ')';
        }

        // Fallback: use raw pretty printer
        $fallback = new Standard();
        return $fallback->prettyPrintExpr($expr);
    }

    private static function propertyFetchToString(Expr\PropertyFetch $expr, PrinterInterface $printer): string
    {
        $parts = [];
        $current = $expr;

        while ($current instanceof Expr\PropertyFetch) {
            $parts[] = $current->name instanceof Identifier ? $current->name->toString() : '';
            $current = $current->var;
        }

        $base = self::toString($current, $printer);
        return $base . '.' . implode('.', array_reverse($parts));
    }

    private static function arrayDimToDot(Expr\ArrayDimFetch $expr, PrinterInterface $printer): string
    {
        $parts = [];

        while ($expr instanceof Expr\ArrayDimFetch) {
            $dim = $expr->dim;

            if ($dim instanceof Node\Scalar\String_) {
                $parts[] = $dim->value;
            } elseif ($dim instanceof Expr) {
                $dimString = self::toString($dim, $printer);
                $parts[] = trim($dimString, '"\'');
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
            case Expr\BinaryOp\Mul::class:
                $op = '*';
                break;
            case Expr\BinaryOp\Div::class:
                $op = '/';
                break;
            case Expr\BinaryOp\Mod::class:
                $op = '%';
                break;
            case Expr\BinaryOp\Pow::class:
                $op = '**';
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
            case Expr\BinaryOp\Coalesce::class:
                $op = '??';
                break;
            default:
                return "{# unsupported operator: " . basename(str_replace('\\', '/', $class)) . " #}";
        }

        return "$left $op $right";
    }
}
