<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class ArrayPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Array_;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var Array_ $node */

        if (empty($node->items)) {
            return '{}';
        }

        $items = array_map(function (ArrayItem $item) use ($printer) {
            $key = $item->key ? $printer->convertNode($item->key) : null;
            $value = $printer->convertNode($item->value);

            return $key !== null ? "$key: $value" : $value;
        }, $node->items);

        return '{ ' . implode(', ', $items) . ' }';
    }
}