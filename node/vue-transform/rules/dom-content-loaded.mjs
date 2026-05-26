import { isDOMContentLoaded, isFn } from '../context.mjs';

export default {
    name: 'dom-content-loaded',
    test: (node) => isDOMContentLoaded(node),
    apply(node, _parent, ctx) {
        const fn = node.arguments[1];
        ctx.vueImports.add('onMounted');
        ctx.recordPartial(node.start, fn.start, 'onMounted(', fn.end, node.end, ')');
    },
};
