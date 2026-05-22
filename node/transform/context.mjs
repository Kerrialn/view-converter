/**
 * Creates the shared transformation context threaded through every rule.
 * Also exports the AST detector predicates so rules can import them directly.
 */

export function createContext(source) {
    const replacements = [];
    const processed    = new Set();

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

    return { source, replacements, processed, src, record, recordPartial };
}

// ─── AST detector predicates (stateless, importable by rules) ────────────────

export function isJqueryCall(node) {
    return (
        node.type === 'CallExpression' &&
        node.callee.type === 'Identifier' &&
        (node.callee.name === '$' || node.callee.name === 'jQuery')
    );
}

export function isJqueryCallWithIdent(node, name) {
    return (
        isJqueryCall(node) &&
        node.arguments.length === 1 &&
        node.arguments[0].type === 'Identifier' &&
        node.arguments[0].name === name
    );
}

export function isDirectChain(node) {
    return (
        node.type === 'CallExpression' &&
        node.callee.type === 'MemberExpression' &&
        isJqueryCall(node.callee.object)
    );
}

export function isTwoLevelChain(node) {
    return (
        node.type === 'CallExpression' &&
        node.callee.type === 'MemberExpression' &&
        isDirectChain(node.callee.object)
    );
}

export function isStaticJquery(node) {
    return (
        node.type === 'CallExpression' &&
        node.callee.type === 'MemberExpression' &&
        node.callee.object.type === 'Identifier' &&
        (node.callee.object.name === '$' || node.callee.object.name === 'jQuery') &&
        node.callee.property.type === 'Identifier'
    );
}

export function isJqueryIIFE(node) {
    if (node.type !== 'CallExpression' || node.callee.type !== 'FunctionExpression') return false;
    if (node.callee.params.length < 1) return false;
    const param = node.callee.params[0];
    if (param.type !== 'Identifier' || (param.name !== '$' && param.name !== 'jQuery')) return false;
    if (node.arguments.length < 1) return false;
    const arg = node.arguments[0];
    return arg.type === 'Identifier' && (arg.name === '$' || arg.name === 'jQuery');
}

export function isFn(node) {
    return node.type === 'FunctionExpression' || node.type === 'ArrowFunctionExpression';
}

export function isChainedFurther(node, parent) {
    return !!(parent && parent.type === 'MemberExpression' && parent.object === node);
}
