<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class StringPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof String_;
    }
    public function print(Node $node, PrinterInterface $printer): string
    {
        if ($node instanceof Node\Scalar\String_) {
            return "'" . $node->value . "'";
        }

        return '{# unsupported node: ' . get_class($node) . ' #}';
    }


}