<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class ConstFetchPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof ConstFetch;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        return strtolower((string) $node->name);
    }
}
