<?php

namespace PhpToTwig\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;

final class ForeachPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Foreach_;
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var Foreach_ $node */

        $iterable = $printer->exprToString($node->expr);
        $key = $node->keyVar ? $printer->exprToString($node->keyVar) : null;
        $value = $printer->exprToString($node->valueVar);

        $loopVars = $key ? "$key, $value" : $value;

        $output = "{% for $loopVars in $iterable %}";

        foreach ($node->stmts as $stmt) {
            $output .= "\n" . $printer->convertNode($stmt);
        }

        $output .= "\n{% endfor %}";

        return $output;
    }
}
