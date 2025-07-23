<?php

namespace ViewConverter\Parser;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;

class ParserHelper
{
    /**
     * @param Name|Expr|string|null $nameNode
     * @param callable $exprToString
     * @return string
     */
    public static function getCallableName($nameNode, callable $exprToString): string
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