import { isJqueryIIFE } from '../context.mjs';

export default {
    name: 'jquery-iife',
    test: (node) => isJqueryIIFE(node),
    apply(node, _parent, ctx) {
        const fn      = node.callee;
        const params  = fn.params;
        const args    = node.arguments;

        const realStmts = fn.body.body.filter(s => !(
            s.type === 'ExpressionStatement' &&
            (s.directive === 'use strict' ||
                (s.expression && s.expression.type === 'Literal' && s.expression.value === 'use strict'))
        ));
        if (realStmts.length === 0) return;

        const first = realStmts[0];
        const last  = realStmts[realStmts.length - 1];

        // The CallExpression node starts at 'function'; the outer grouping '(' is one char back.
        let prefixStart = node.start;
        while (prefixStart > 0 && ctx.source[prefixStart - 1] === '(') prefixStart--;

        let suffixEnd = node.end;
        while (suffixEnd < ctx.source.length && ctx.source[suffixEnd] === ')') suffixEnd++;
        while (suffixEnd < ctx.source.length && ctx.source[suffixEnd] === ';') suffixEnd++;

        // Extra params beyond $ need const declarations at the call-site indent level.
        const extraParams = params.slice(1).filter(p => p.type === 'Identifier');
        const extraArgs   = args.slice(1);

        let prefixReplacement = '';
        if (extraParams.length > 0 && extraArgs.length >= extraParams.length) {
            const iifeLineStart = ctx.source.lastIndexOf('\n', prefixStart - 1) + 1;
            const iifeIndent    = ctx.source.slice(iifeLineStart, prefixStart);
            const consts = extraParams
                .map((p, i) => `const ${p.name} = ${ctx.src(extraArgs[i])};`)
                .join(`\n${iifeIndent}`);
            prefixReplacement = `${consts}\n${iifeIndent}`;
        }

        ctx.record(prefixStart, first.start, prefixReplacement);
        ctx.record(last.end, suffixEnd, '');
    },
};
