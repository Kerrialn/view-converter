export default {
    name: 'var-to-let',
    test: (node) => node.type === 'VariableDeclaration' && node.kind === 'var',
    apply: (node, _parent, ctx) => ctx.record(node.start, node.start + 3, 'let'),
};
