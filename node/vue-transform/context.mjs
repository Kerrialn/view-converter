export function createContext(source) {
    const replacements = [];
    const processed    = new Set();
    const vueImports   = new Set();
    const domRefs      = new Map(); // id string → camelCase ref name

    function src(node) {
        return source.slice(node.start, node.end);
    }

    function record(start, end, code) {
        if (!processed.has(start)) {
            replacements.push({ start, end, code });
            processed.add(start);
        }
    }

    function recordPartial(ps, pe, pc, ss, se, sc) {
        if (!processed.has(ps)) {
            record(ps, pe, pc);
            if (sc !== null) record(ss, se, sc);
        }
    }

    return { source, replacements, processed, vueImports, domRefs, src, record, recordPartial };
}

// ─── AST predicates ───────────────────────────────────────────────────────────

export function isFn(node) {
    return node.type === 'FunctionExpression' || node.type === 'ArrowFunctionExpression';
}

export function isDOMContentLoaded(node) {
    return (
        node.type === 'CallExpression' &&
        node.callee.type === 'MemberExpression' &&
        node.callee.object.type === 'Identifier' &&
        node.callee.object.name === 'document' &&
        node.callee.property.name === 'addEventListener' &&
        node.arguments.length >= 2 &&
        node.arguments[0].type === 'Literal' &&
        node.arguments[0].value === 'DOMContentLoaded' &&
        isFn(node.arguments[1])
    );
}

export function isDOMQuery(node) {
    if (node.type !== 'CallExpression') return false;
    if (node.callee.type !== 'MemberExpression') return false;
    if (node.callee.object.type !== 'Identifier') return false;
    if (node.callee.object.name !== 'document') return false;
    const m = node.callee.property.name;
    return m === 'getElementById' || m === 'querySelector' || m === 'querySelectorAll';
}
