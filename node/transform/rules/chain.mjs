import { isDirectChain, isJqueryCall, isChainedFurther } from '../context.mjs';
import { extractSelector, transformMethod } from '../helpers.mjs';

export default {
    name: 'chain',
    test(node, parent) {
        // Single-level $(sel).method() — fall-through after chain-fn
        if (isDirectChain(node)) return !isChainedFurther(node, parent);
        // Bare $(selector) with no further method call
        if (isJqueryCall(node) && node.arguments.length === 1) return !isChainedFurther(node, parent);
        return false;
    },
    apply(node, _parent, ctx) {
        // Bare $(selector)
        if (isJqueryCall(node)) {
            const selInfo = extractSelector(node, ctx.src);
            if (selInfo) ctx.record(node.start, node.end, selInfo.expr);
            return;
        }

        // $(sel).method()
        const selInfo = extractSelector(node.callee.object, ctx.src);
        if (!selInfo) return;
        const code = transformMethod(node.callee.property.name, selInfo, node.arguments, ctx.src);
        if (code !== null) ctx.record(node.start, node.end, code);
    },
};
