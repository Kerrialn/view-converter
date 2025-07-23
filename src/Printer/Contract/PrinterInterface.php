<?php

namespace ViewConverter\Printer\Contract;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;

interface PrinterInterface
{
    public function print(array $nodes): string;

    public function convertNode(Node $node): string;

    /**
     * @param Name|Expr|null $expr
     */
    public function exprToString($expr): string;
}