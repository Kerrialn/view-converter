<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Echo_;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

final class EchoPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Echo_;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        // Skip if the echo has no expressions
        if (empty($node->exprs)) {
            return '';
        }

        $expr = $node->exprs[0] ?? null;

        if (! $expr instanceof Node) {
            return '';
        }

        $output = $printer->convertNode($expr);

        $trimmed = trim($output);
        if (str_starts_with($trimmed, '{%') || str_starts_with($trimmed, '{{') || str_starts_with($trimmed, '{#')) {
            return $output;
        }

        return '{{ ' . $output . ' }}';
    }

}