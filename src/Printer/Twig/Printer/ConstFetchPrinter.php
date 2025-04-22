<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

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
