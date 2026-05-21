<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class ClassConstFetchPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof ClassConstFetch;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var ClassConstFetch $node */
        $class = $node->class instanceof Name
            ? $node->class->toString()
            : $printer->convertNode($node->class);

        $const = $node->name instanceof Identifier
            ? $node->name->toString()
            : $printer->convertNode($node->name);

        return "constant('$class::$const')";
    }
}
