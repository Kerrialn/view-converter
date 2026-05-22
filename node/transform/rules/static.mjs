import { isStaticJquery, isFn } from '../context.mjs';
import { buildStaticPartial } from '../helpers.mjs';

export default {
    name: 'static-jquery',
    test: (node) => isStaticJquery(node),
    apply(node, _parent, ctx) {
        const method = node.callee.property.name;
        const fnArg  = node.arguments.find(isFn) ?? null;

        if (fnArg !== null) {
            const sp = buildStaticPartial(method, node.arguments, fnArg, ctx.src);
            if (sp !== null) {
                ctx.recordPartial(node.start, fnArg.start, sp.prefix, fnArg.end, node.end, sp.suffix);
                return;
            }
        }

        const code = transformStatic(method, node.arguments, ctx.src);
        if (code !== null) ctx.record(node.start, node.end, code);
    },
};

function transformStatic(method, args, src) {
    const a = i => i < args.length ? src(args[i]) : 'undefined';
    switch (method) {
        case 'each':      return `${a(0)}.forEach((v, i) => (${a(1)})(i, v))`;
        case 'extend':    return `Object.assign(${args.map((_, i) => a(i)).join(', ')})`;
        case 'trim':      return `${a(0)}.trim()`;
        case 'isArray':   return `Array.isArray(${a(0)})`;
        case 'inArray':   return `Array.prototype.indexOf.call(${a(1)}, ${a(0)})`;
        case 'map':       return `${a(0)}.map((v, i) => (${a(1)})(i, v))`;
        case 'grep':      return `${a(0)}.filter(${a(1)})`;
        case 'now':       return `Date.now()`;
        case 'parseJSON': return `JSON.parse(${a(0)})`;
        case 'type':      return `typeof ${a(0)}`;
        case 'get': {
            const base = `fetch(${a(0)}).then((r) => r.text())`;
            return args.length > 1 ? `${base}.then(${a(1)})` : base;
        }
        case 'post': {
            const bodyStr = args.length > 1 ? `, { method: 'POST', body: JSON.stringify(${a(1)}) }` : `, { method: 'POST' }`;
            const base    = `fetch(${a(0)}${bodyStr})`;
            return args.length > 2 ? `${base}.then((r) => r.text()).then(${a(2)})` : base;
        }
        case 'getJSON': {
            const base = `fetch(${a(0)}).then((r) => r.json())`;
            return args.length > 1 ? `${base}.then(${a(1)})` : base;
        }
        case 'ajax':      return `/* TODO: migrate $.ajax to fetch */ fetch(/* url, options */)`;
        default:          return null;
    }
}
