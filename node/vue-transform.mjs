#!/usr/bin/env node
/**
 * Transforms vanilla JS into a Vue 3 Single File Component skeleton.
 *
 * Strategy:
 *   1. Parse with acorn, walk bottom-up, apply rules (same engine as jquery-transform).
 *   2. Rules record source-range replacements and register needed Vue imports.
 *   3. Apply replacements end-to-start, then wrap result in SFC structure.
 *
 * Output is a .vue file written to stdout — redirect to <component>.vue.
 * To add new transformations: create a rule in vue-transform/rules/ and
 * register it in vue-transform/rules.mjs.
 */
import { readFileSync } from 'fs';
import { parse } from 'acorn';
import { createContext } from './vue-transform/context.mjs';
import { processHtml } from './vue-transform/html-processor.mjs';
import rules from './vue-transform/rules.mjs';

const filePath  = process.argv[2];
const htmlPath  = process.argv[3] ?? null;

if (!filePath) {
    process.stderr.write('Usage: node vue-transform.mjs <file.js> [template.html]\n');
    process.exit(1);
}

const source = readFileSync(filePath, 'utf8');

let ast;
try {
    ast = parse(source, { ecmaVersion: 'latest', sourceType: 'script' });
} catch (_) {
    try {
        ast = parse(source, { ecmaVersion: 'latest', sourceType: 'module' });
    } catch (e) {
        process.stderr.write(`Parse error: ${e.message}\n`);
        process.exit(1);
    }
}

const ctx = createContext(source);
walk(ast, null);

ctx.replacements.sort((a, b) => b.start - a.start);

let result = source;
for (const { start, end, code } of ctx.replacements) {
    result = result.slice(0, start) + code + result.slice(end);
}

// Build sorted import line from whatever rules registered
const imports = [...ctx.vueImports].sort();
const importLine = imports.length > 0
    ? `import { ${imports.join(', ')} } from 'vue';\n\n`
    : '';

const templateContent = htmlPath
    ? processHtml(htmlPath, ctx.domRefs)
    : '    <!-- TODO: migrate HTML template here -->';

process.stdout.write(
`<template>
${templateContent}
</template>

<script setup>
${importLine}${result.trimEnd()}
</script>
`);

// ─── Bottom-up walker (identical to jquery-transform) ────────────────────────

function walk(node, parent) {
    if (!node || typeof node !== 'object' || !node.type) return;

    for (const key of Object.keys(node)) {
        if (key === 'type' || key === 'start' || key === 'end' || key === 'loc') continue;
        const child = node[key];
        if (Array.isArray(child))                                child.forEach(c => walk(c, node));
        else if (child && typeof child === 'object' && child.type) walk(child, node);
    }

    if (ctx.processed.has(node.start)) return;

    for (const rule of rules) {
        if (rule.test(node, parent, ctx)) {
            rule.apply(node, parent, ctx);
            break;
        }
    }
}
