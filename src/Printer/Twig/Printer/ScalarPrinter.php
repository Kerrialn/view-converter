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
        if ($node instanceof Scalar\Encapsed) {
            return $this->printEncapsed($node, $printer);
        }

        if ($node instanceof Scalar\String_) {
            return "'" . addslashes($node->value) . "'";
        }

        if ($node instanceof Scalar\Int_ || $node instanceof Scalar\Float_) {
            return (string) $node->value;
        }

        return '{# unsupported scalar #}';
    }

    private function printEncapsed(Scalar\Encapsed $node, PrinterInterface $printer): string
    {
        $parts = [];

        foreach ($node->parts as $part) {
            if ($part instanceof Scalar\EncapsedStringPart) {
                if ($part->value !== '') {
                    $parts[] = "'" . addslashes($part->value) . "'";
                }
            } else {
                $parts[] = $printer->convertNode($part);
            }
        }

        if (empty($parts)) {
            return "''";
        }

        return implode(' ~ ', $parts);
    }
}
