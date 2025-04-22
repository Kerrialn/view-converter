<?php

namespace PhpToTwig\Parser;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;

class ParserHelper
{
    public static function getCallableName(Name|Expr|string|null $nameNode, callable $exprToString): string
    {
        if ($nameNode === null) {
            return '';
        }

        if ($nameNode instanceof Name) {
            return $nameNode->toString();
        }

        if ($nameNode instanceof Expr) {
            return $exprToString($nameNode);
        }

        return (string) $nameNode;
    }


}