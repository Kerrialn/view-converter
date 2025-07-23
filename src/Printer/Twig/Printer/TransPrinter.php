<?php

namespace ViewConverter\Printer\Twig\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use ViewConverter\Parser\ParserHelper;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;

final class TransPrinter implements NodePrinterInterface
{
    public function supports(Node $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        $funcName = ParserHelper::getCallableName($node->name, fn($e) => '');

        return in_array(strtolower($funcName), ['translate', 'lang'], true);
    }

    public function print(Node $node, PrinterInterface $printer): string
    {
        /** @var FuncCall $node */
        $funcName = ParserHelper::getCallableName($node->name, fn($e) => '');

        // Pull the key argument regardless of nesting
        $argNode = $node->args[0]->value;

        // If the call is translate(lang('...')), unwrap it
        if ($argNode instanceof FuncCall) {
            $innerName = ParserHelper::getCallableName($argNode->name, fn($e) => '');
            if (strtolower($innerName) === 'lang') {
                $argNode = $argNode->args[0]->value;
            }
        }

        $key = $printer->exprToString($argNode);
        $key = trim($key, '"\'');
        $key = str_replace(':', '.', $key);

        return "trans('$key')";
    }

}
