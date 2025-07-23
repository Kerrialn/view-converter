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
        return match (true) {
            $node instanceof Scalar\String_ => "'" . $node->value . "'",
            $node instanceof Scalar\LNumber,
                $node instanceof Scalar\DNumber => (string) $node->value,
            default => '{# unsupported scalar #}'
        };
    }
}