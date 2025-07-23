<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Scalar;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class ScalarPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Scalar;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        if ($node instanceof Scalar\String_) {
            return "'" . $node->value . "'";
        }

        if ($node instanceof Scalar\LNumber || $node instanceof Scalar\DNumber) {
            return (string) $node->value;
        }

        return '{# unsupported scalar #}';
    }

}