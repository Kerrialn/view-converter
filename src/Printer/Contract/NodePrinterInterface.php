<?php

namespace ViewConverter\Printer\Contract;

use PhpParser\Node;

interface NodePrinterInterface
{
    public function supports(Node $node): bool;

    public function print(Node $node, PrinterInterface $printer): string;
}