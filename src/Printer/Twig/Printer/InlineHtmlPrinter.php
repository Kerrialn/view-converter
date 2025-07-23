<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Stmt\InlineHTML;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class InlineHtmlPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof InlineHTML;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var InlineHTML $node */
        return rtrim($node->value);
    }
}
