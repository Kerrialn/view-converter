<?php

namespace PhpToTwig\Printer\Contract;

use PhpParser\Node;

interface PrinterInterface
{
    public function print(array $nodes): string;

    public function convertNode(Node $node): string;

    public function exprToString(Node\Name|Node\Expr|null $expr): string;
}