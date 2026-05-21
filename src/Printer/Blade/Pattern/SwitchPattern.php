<?php

namespace ViewConverter\Printer\Blade\Pattern;

use ViewConverter\Printer\Contract\BladePatternInterface;
use ViewConverter\Util\BladeExpressionHelper;

final class SwitchPattern implements BladePatternInterface
{
    public function apply(string $content): string
    {
        return preg_replace_callback(
            '/@switch\s*\((.+?)\)(.*?)@endswitch/s',
            function ($m) {
                $subject = BladeExpressionHelper::convertExpr(trim($m[1]));
                $body    = preg_replace('/@break\b/', '', $m[2]);

                preg_match_all('/@case\s*\((.+?)\)(.*?)(?=@case|@default|$)/s', $body, $cases, PREG_SET_ORDER);

                $out = '';
                foreach ($cases as $i => $case) {
                    $val      = BladeExpressionHelper::convertExpr(trim($case[1]));
                    $caseBody = trim($case[2]);
                    $out .= ($i === 0 ? "{% if $subject == $val %}" : "\n{% elseif $subject == $val %}");
                    $out .= "\n$caseBody";
                }

                if (preg_match('/@default(.*?)$/s', $body, $default)) {
                    $out .= "\n{% else %}\n" . trim($default[1]);
                }

                return $out . "\n{% endif %}";
            },
            $content
        );
    }
}
