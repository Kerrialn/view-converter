<?php

namespace ViewConverter\Printer\Twig;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use ViewConverter\Printer\Contract\NodePrinterInterface;
use ViewConverter\Printer\Contract\PrinterInterface;
use ViewConverter\Printer\Twig\Printer\ArrayDimFetchPrinter;
use ViewConverter\Printer\Twig\Printer\ArrayPrinter;
use ViewConverter\Printer\Twig\Printer\BinaryOpPrinter;
use ViewConverter\Printer\Twig\Printer\BooleanNotPrinter;
use ViewConverter\Printer\Twig\Printer\ConcatPrinter;
use ViewConverter\Printer\Twig\Printer\EchoPrinter;
use ViewConverter\Printer\Twig\Printer\ExpressionPrinter;
use ViewConverter\Printer\Twig\Printer\ForeachPrinter;
use ViewConverter\Printer\Twig\Printer\FuncCallPrinter;
use ViewConverter\Printer\Twig\Printer\IfPrinter;
use ViewConverter\Printer\Twig\Printer\InlineHtmlPrinter;
use ViewConverter\Printer\Twig\Printer\MethodCallPrinter;
use ViewConverter\Printer\Twig\Printer\CastPrinter;
use ViewConverter\Printer\Twig\Printer\PropertyFetchPrinter;
use ViewConverter\Printer\Twig\Printer\PyroThemeLoaderPrinter;
use ViewConverter\Printer\Twig\Printer\ScalarPrinter;
use ViewConverter\Printer\Twig\Printer\StringPrinter;
use ViewConverter\Printer\Twig\Printer\TernaryPrinter;
use ViewConverter\Printer\Twig\Printer\TransPrinter;
use ViewConverter\Printer\Twig\Printer\VariablePrinter;
use ViewConverter\Printer\Twig\Printer\ViewLoaderPrinter;
use ViewConverter\Util\ExpressionHelper;

final class TwigPrinter implements PrinterInterface
{
    /** @var NodePrinterInterface[] */
    private array $nodePrinters;

    public function __construct(array $nodePrinters = [])
    {
        $this->nodePrinters = $nodePrinters ?: self::defaultPrinters();
    }

    public function print(array $nodes): string
    {
        return implode("\n", array_map([$this, 'convertNode'], $nodes));
    }

    public function convertNode(Node $node): string
    {


        foreach ($this->nodePrinters as $printer) {
            if ($printer->supports($node)) {
                return $printer->print($node, $this);
            }
        }

        return '{# unsupported node: ' . get_class($node) . ' #}';
    }

    /**
     * @param Name|Expr|null $expr
     */
    public function exprToString($expr): string
    {
        return ExpressionHelper::toString($expr, $this);
    }

    public static function defaultPrinters(): array
    {
        return [
            new VariablePrinter(),
            new PropertyFetchPrinter(),
            new StringPrinter(),
            new EchoPrinter(),
            new ArrayPrinter(),
            new CastPrinter(),
            new PyroThemeLoaderPrinter(),
            new ViewLoaderPrinter(),
            new MethodCallPrinter(),
            new ExpressionPrinter(),
            new IfPrinter(),
            new TransPrinter(),
            new TernaryPrinter(),
            new ConcatPrinter(),
            new ScalarPrinter(),
            new BinaryOpPrinter(),
            new FuncCallPrinter(),
            new BooleanNotPrinter(),
            new InlineHtmlPrinter(),
            new ForeachPrinter(),
            new ArrayDimFetchPrinter(),
        ];
    }
}
