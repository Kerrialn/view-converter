import { isJqueryCall, isDirectChain, isJqueryCallWithIdent, isFn } from '../context.mjs';

const PREFIX = `document.addEventListener('DOMContentLoaded', `;

export default {
    name: 'ready',
    test(node) {
        // $(fn) shorthand
        if (isJqueryCall(node) && node.arguments.length === 1 && isFn(node.arguments[0])) return true;
        // $(document).ready(fn)
        if (
            isDirectChain(node) &&
            node.callee.property.name === 'ready' &&
            isJqueryCallWithIdent(node.callee.object, 'document') &&
            node.arguments.length >= 1
        ) return true;
        return false;
    },
    apply(node, _parent, ctx) {
        const fn = node.arguments[0];
        ctx.recordPartial(node.start, fn.start, PREFIX, null, null, null);
    },
};
