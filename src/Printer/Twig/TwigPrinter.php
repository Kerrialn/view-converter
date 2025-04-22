<?php

namespace PhpToTwig\Printer\Twig;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\PrettyPrinter\Standard;
use PhpToTwig\Printer\Contract\NodePrinterInterface;
use PhpToTwig\Printer\Contract\PrinterInterface;
use PhpToTwig\Printer\Twig\Printer\ArrayDimFetchPrinter;
use PhpToTwig\Printer\Twig\Printer\ArrayPrinter;
use PhpToTwig\Printer\Twig\Printer\BinaryOpPrinter;
use PhpToTwig\Printer\Twig\Printer\BooleanNotPrinter;
use PhpToTwig\Printer\Twig\Printer\ConcatPrinter;
use PhpToTwig\Printer\Twig\Printer\EchoPrinter;
use PhpToTwig\Printer\Twig\Printer\ExpressionPrinter;
use PhpToTwig\Printer\Twig\Printer\ForeachPrinter;
use PhpToTwig\Printer\Twig\Printer\FuncCallPrinter;
use PhpToTwig\Printer\Twig\Printer\IfPrinter;
use PhpToTwig\Printer\Twig\Printer\InlineHtmlPrinter;
use PhpToTwig\Printer\Twig\Printer\MethodCallPrinter;
use PhpToTwig\Printer\Twig\Printer\CastPrinter;
use PhpToTwig\Printer\Twig\Printer\PropertyFetchPrinter;
use PhpToTwig\Printer\Twig\Printer\PyroThemeLoaderPrinter;
use PhpToTwig\Printer\Twig\Printer\ScalarPrinter;
use PhpToTwig\Printer\Twig\Printer\StringPrinter;
use PhpToTwig\Printer\Twig\Printer\TernaryPrinter;
use PhpToTwig\Printer\Twig\Printer\TransPrinter;
use PhpToTwig\Printer\Twig\Printer\VariablePrinter;
use PhpToTwig\Printer\Twig\Printer\ViewLoaderPrinter;
use PhpToTwig\Util\ExpressionHelper;

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

    public function exprToString(Name|Expr|null $expr): string
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
