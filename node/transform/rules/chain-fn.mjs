import { isDirectChain, isChainedFurther, isFn } from '../context.mjs';
import { extractSelector, buildPartialReplacement, EVENT_SHORTCUTS } from '../helpers.mjs';

const FN_METHODS = new Set(['on', 'off', 'each', ...EVENT_SHORTCUTS]);

export default {
    name: 'chain-fn',
    test(node, parent) {
        if (!isDirectChain(node) || isChainedFurther(node, parent)) return false;
        return FN_METHODS.has(node.callee.property.name) && node.arguments.some(isFn);
    },
    apply(node, _parent, ctx) {
        const selInfo = extractSelector(node.callee.object, ctx.src);
        if (!selInfo) return;

        const partial = buildPartialReplacement(node.callee.property.name, selInfo, node.arguments, ctx.src);
        if (!partial?.fnArg) return;

        const fn = partial.fnArg;
        ctx.recordPartial(node.start, fn.start, partial.prefix, fn.end, node.end, partial.suffix);
    },
};
