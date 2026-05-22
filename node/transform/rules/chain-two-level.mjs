import { isTwoLevelChain, isChainedFurther } from '../context.mjs';
import { extractSelector, transformMethod, resolveTraversal, methodBody } from '../helpers.mjs';

export default {
    name: 'chain-two-level',
    test: (node, parent) => isTwoLevelChain(node) && !isChainedFurther(node, parent),
    apply(node, _parent, ctx) {
        const inner   = node.callee.object;
        const method2 = node.callee.property.name;
        const method1 = inner.callee.property.name;
        const args1   = inner.arguments;
        const args2   = node.arguments;

        const selInfo = extractSelector(inner.callee.object, ctx.src);
        if (!selInfo) return;

        const { expr, isMultiple } = selInfo;
        const a1 = i => i < args1.length ? ctx.src(args1[i]) : 'undefined';

        const traversal = resolveTraversal(method1, a1, expr, isMultiple);
        if (traversal !== null) {
            const code = transformMethod(method2, traversal, args2, ctx.src);
            if (code !== null) ctx.record(node.start, node.end, code);
            return;
        }

        const body1 = methodBody(method1, args1, ctx.src);
        const body2 = methodBody(method2, args2, ctx.src);
        if (body1 !== null && body2 !== null) {
            const sub = s => s.replace(/\bEL\b/g, 'el');
            const raw = s => s.replace(/\bEL\b/g, expr);
            const code = isMultiple
                ? `${expr}.forEach((el) => { ${sub(body1)}; ${sub(body2)}; })`
                : `${raw(body1)}; ${raw(body2)}`;
            ctx.record(node.start, node.end, code);
        }
    },
};
