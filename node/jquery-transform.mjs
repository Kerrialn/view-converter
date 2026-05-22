#!/usr/bin/env node
/**
 * Transforms jQuery patterns to vanilla JS using source-range replacement.
 *
 * Strategy:
 *   1. Parse with acorn to get an AST with character positions.
 *   2. Walk bottom-up (children first) so inner jQuery calls are recorded
 *      before outer wrappers — this prevents outer full-replacements from
 *      overwriting inner transforms.
 *   3. Each rule records partial or full source-range replacements via ctx.
 *   4. Apply all replacements end-to-start so earlier positions stay valid.
 *
 * To add a new transformation: create a rule file in transform/rules/ and
 * register it in transform/rules.mjs.
 */
import { readFileSync } from 'fs';
import { parse } from 'acorn';
import { createContext } from './transform/context.mjs';
import rules from './transform/rules.mjs';

const filePath = process.argv[2];
if (!filePath) {
    process.stderr.write('Usage: node jquery-transform.mjs <file.js>\n');
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

process.stdout.write(result);

// ─── Bottom-up walker ────────────────────────────────────────────────────────

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
