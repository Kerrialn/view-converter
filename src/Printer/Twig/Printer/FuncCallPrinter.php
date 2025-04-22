<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpToTwig\Parser\ParserHelper;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

final class FuncCallPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof FuncCall;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        $name = ParserHelper::getCallableName($node->name, [$printer, 'exprToString']);
        $args = array_map(
            fn($arg) => $printer->convertNode($arg->value),
            $node->args
        );

        return $name . '(' . implode(', ', $args) . ')';
    }
}
