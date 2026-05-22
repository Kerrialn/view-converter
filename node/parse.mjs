#!/usr/bin/env node
/**
 * Parses a legacy JS file with acorn and extracts Stimulus-relevant patterns:
 *   - Root selector (document.querySelectorAll)
 *   - Child targets (element.querySelector / querySelectorAll)
 *   - Event listeners → actions + method bodies
 *   - dataset accesses → Stimulus values
 * Outputs a single JSON object to stdout.
 */
import { readFileSync } from 'fs';
import { parse } from 'acorn';
import { basename, extname } from 'path';

const filePath = process.argv[2];
if (!filePath) {
    process.stderr.write('Usage: node parse.mjs <file.js>\n');
    process.exit(1);
}

const source = readFileSync(filePath, 'utf8');
const fileName = basename(filePath, extname(filePath));

let ast;
try {
    ast = parse(source, { ecmaVersion: 2020, sourceType: 'script' });
} catch {
    try {
        ast = parse(source, { ecmaVersion: 2020, sourceType: 'module' });
    } catch (e) {
        process.stdout.write(JSON.stringify({ error: e.message }) + '\n');
        process.exit(1);
    }
}

// varName → { selector, method, isDocument }
const varSelectors = {};

// Collected results
const actions = [];       // { event, targetVar, methodName, params, bodySnippet }
const valueNames = [];    // unique dataset property names
const warnings = [];

// ---- AST walker ----

function walk(node, parent) {
    if (!node || typeof node !== 'object' || !node.type) return;

    if (node.type === 'VariableDeclarator' && node.init) {
        const info = extractQueryCall(node.init);
        if (info && node.id && node.id.type === 'Identifier') {
            varSelectors[node.id.name] = info;
        }
    }

    if (node.type === 'AssignmentExpression' && node.right) {
        const info = extractQueryCall(node.right);
        if (info && node.left && node.left.type === 'Identifier') {
            varSelectors[node.left.name] = info;
        }
    }

    if (node.type === 'CallExpression') {
        handleCallExpression(node);
    }

    // dataset.foo access
    if (
        node.type === 'MemberExpression' &&
        node.object.type === 'MemberExpression' &&
        node.object.property.type === 'Identifier' &&
        node.object.property.name === 'dataset' &&
        node.property.type === 'Identifier'
    ) {
        const name = node.property.name;
        if (!valueNames.includes(name)) {
            valueNames.push(name);
        }
    }

    for (const key of Object.keys(node)) {
        if (key === 'type' || key === 'start' || key === 'end') continue;
        const child = node[key];
        if (Array.isArray(child)) {
            child.forEach(c => walk(c, node));
        } else if (child && typeof child === 'object' && child.type) {
            walk(child, node);
        }
    }
}

function handleCallExpression(node) {
    if (node.callee.type !== 'MemberExpression') return;
    const prop = node.callee.property.name;
    if (prop !== 'addEventListener') return;
    if (node.arguments.length < 2) return;

    const eventArg = node.arguments[0];
    const handlerArg = node.arguments[1];
    if (!eventArg || eventArg.type !== 'Literal') return;

    const event = eventArg.value;
    const targetObj = node.callee.object;
    const targetVar = targetObj.type === 'Identifier' ? targetObj.name : null;
    const isDocument = targetVar === 'document';

    if (isDocument) {
        warnings.push(
            `Document-level '${event}' listener — this is likely an outside-click pattern; migrate using connect()/disconnect() lifecycle callbacks`
        );
        return;
    }

    let methodName, params = ['event'], bodySnippet = null;

    if (handlerArg.type === 'Identifier') {
        methodName = handlerArg.name;
    } else if (
        handlerArg.type === 'FunctionExpression' ||
        handlerArg.type === 'ArrowFunctionExpression'
    ) {
        methodName = generateMethodName(event, targetVar);
        params = handlerArg.params.length > 0
            ? handlerArg.params.map(p => p.name || 'event')
            : ['event'];
        bodySnippet = extractBody(handlerArg);
    } else {
        methodName = generateMethodName(event, targetVar);
    }

    if (!actions.find(a => a.methodName === methodName)) {
        actions.push({ event, targetVar, methodName, params, bodySnippet });
    }
}

walk(ast, null);

// ---- Derive controller structure ----

// Root: first document.querySelectorAll (or querySelector)
let rootSelector = null;
let rootVarName = null;
for (const [varName, info] of Object.entries(varSelectors)) {
    if (info.isDocument && info.method === 'querySelectorAll') {
        rootSelector = info.selector;
        rootVarName = varName;
        break;
    }
}
if (!rootSelector) {
    for (const [varName, info] of Object.entries(varSelectors)) {
        if (info.isDocument) {
            rootSelector = info.selector;
            rootVarName = varName;
            break;
        }
    }
}

// Targets: child selectors (non-document, or non-root document ones)
const targets = [];
const seenTargets = new Set();

for (const [varName, info] of Object.entries(varSelectors)) {
    if (info.isDocument && info.selector === rootSelector) continue;
    const targetName = varToTargetName(varName, info.selector);
    if (!seenTargets.has(targetName)) {
        seenTargets.add(targetName);
        targets.push({ name: targetName, selector: info.selector, variableName: varName });
    }
}

// Actions — resolve targetVar to target name
const resolvedActions = actions.map(a => {
    const info = varSelectors[a.targetVar];
    const targetName = info
        ? varToTargetName(a.targetVar, info.selector)
        : (a.targetVar || 'element');
    return { event: a.event, targetName, methodName: a.methodName };
});

// Methods — from actions with body snippets
const methods = [];
for (const a of actions) {
    if (a.bodySnippet !== null) {
        methods.push({ name: a.methodName, params: a.params, body: a.bodySnippet });
    }
}

const values = valueNames.map(name => ({ name, stimulusType: 'String' }));

process.stdout.write(JSON.stringify({
    controllerName: toKebabCase(fileName),
    rootSelector,
    targets,
    actions: resolvedActions,
    values,
    methods,
    warnings,
}, null, 2) + '\n');

// ---- Helpers ----

function extractQueryCall(node) {
    if (node.type !== 'CallExpression') return null;
    if (node.callee.type !== 'MemberExpression') return null;

    const prop = node.callee.property.name;
    const valid = ['querySelector', 'querySelectorAll', 'getElementById', 'getElementsByClassName'];
    if (!valid.includes(prop)) return null;

    const obj = node.callee.object;
    const isDocument = obj.type === 'Identifier' && obj.name === 'document';

    let selector = null;
    if (node.arguments[0] && node.arguments[0].type === 'Literal') {
        selector = prop === 'getElementById'
            ? '#' + node.arguments[0].value
            : node.arguments[0].value;
    }

    return { selector, method: prop, isDocument };
}

function varToTargetName(varName, selector) {
    // Prefer using the variable name — it's usually more semantic than the selector
    let name = varName;
    // Singularize naive plurals (items → item, dropdowns → dropdown)
    if (name.endsWith('s') && name.length > 3) name = name.slice(0, -1);
    return name;
}

function generateMethodName(event, varName) {
    const cap = s => s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
    return 'handle' + cap(varName || 'element') + cap(event);
}

function extractBody(funcNode) {
    const body = funcNode.body;
    if (body.type === 'BlockStatement') {
        const inner = source.slice(body.start + 1, body.end - 1);
        return deindent(inner);
    }
    return source.slice(body.start, body.end);
}

function deindent(str) {
    const lines = str.split('\n');
    const nonEmpty = lines.filter(l => l.trim().length > 0);
    if (nonEmpty.length === 0) return str.trim();
    const minIndent = nonEmpty.reduce((min, l) => {
        const m = l.match(/^(\s*)/);
        return Math.min(min, m ? m[1].length : 0);
    }, Infinity);
    return lines.map(l => l.slice(minIndent)).join('\n').trim();
}

function toKebabCase(str) {
    return str
        .replace(/([a-z])([A-Z])/g, '$1-$2')
        .replace(/[\s_]+/g, '-')
        .toLowerCase();
}
