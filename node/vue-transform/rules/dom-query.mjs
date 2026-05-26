import { isDOMQuery } from '../context.mjs';

const HINTS = {
    getElementById:     (sel) => `TODO: ref → const ${varName(sel)} = ref(null); add ref="${sel}" to element`,
    querySelector:      (sel) => `TODO: ref → const el = ref(null); add ref="..." to element`,
    querySelectorAll:   (sel) => `TODO: v-for or ref → const items = ref([]); add ref="..." to element`,
};

export default {
    name: 'dom-query',
    test: (node) => isDOMQuery(node),
    apply(node, _parent, ctx) {
        const method = node.callee.property.name;
        const arg    = node.arguments[0];
        const sel    = arg?.type === 'Literal' ? String(arg.value) : ctx.src(arg);
        const hint   = HINTS[method](sel);
        ctx.vueImports.add('ref');
        ctx.record(node.start, node.start, `/* ${hint} */ `);

        if (method === 'getElementById' && arg?.type === 'Literal') {
            const id = String(arg.value);
            ctx.domRefs.set(id, varName(id));
        }
    },
};

function varName(id) {
    return id.replace(/^#/, '').replace(/-([a-z])/g, (_, c) => c.toUpperCase());
}
