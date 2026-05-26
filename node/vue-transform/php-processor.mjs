/**
 * Converts PHP template syntax to Vue template approximations.
 *
 * - <?= $var ?> / <?php echo $var ?>  →  {{ var }}
 * - if / elseif / else / endif        →  v-if / v-else-if / v-else on next element
 * - foreach / endforeach              →  v-for="val in arr" on next element
 * - for / endfor                      →  <!-- v-for (manual) --> (needs manual conversion)
 * - All other PHP blocks              →  stripped
 *
 * Variable names: $var → var, $arr['key'] → arr.key
 * htmlspecialchars() wrapper is dropped (Vue auto-escapes).
 */
export function processPhpTemplate(html) {
    let result = html.replace(/<\?(?:php\s?|=)([\s\S]*?)\?>/g, convertBlock);
    result = injectDirectives(result);
    return result.replace(/\n{3,}/g, '\n\n');
}

function convertBlock(match, content) {
    const isShortEcho = match.startsWith('<?=');
    const s = content.trim();

    if (isShortEcho)               return `{{ ${toVue(s)} }}`;
    if (/^echo\s+/.test(s))        return `{{ ${toVue(s.replace(/^echo\s+/, ''))} }}`;

    if (/^if\s*\(/.test(s)     && /:\s*$/.test(s)) return `<!-- v-if: ${condToVue(s)} -->`;
    if (/^elseif\s*\(/.test(s) && /:\s*$/.test(s)) return `<!-- v-else-if: ${condToVue(s)} -->`;
    if (/^else\s*:/.test(s))                        return `<!-- v-else -->`;
    if (/^endif\s*;?$/.test(s))                     return `<!-- end-v-if -->`;

    if (/^foreach\s*\(/.test(s) && /:\s*$/.test(s)) return `<!-- v-for: ${forEachToVue(s)} -->`;
    if (/^endforeach\s*;?$/.test(s))                return `<!-- end-v-for -->`;

    if (/^for\s*\(/.test(s)  && /:\s*$/.test(s)) return `<!-- v-for (manual): ${s} -->`;
    if (/^endfor\s*;?$/.test(s))                  return `<!-- end-v-for -->`;

    return ''; // variable assignments, require, include, etc.
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function toVue(expr) {
    return expr
        .replace(/;\s*$/, '')
        .replace(/htmlspecialchars\(\s*([\s\S]*?)\s*\)/g, '$1')
        .replace(/number_format\(\s*([\s\S]*?),\s*\d+\s*\)/g, '$1')
        .replace(/ucfirst\(\s*([\s\S]*?)\s*\)/g, '$1')
        .replace(/date\((?:[^)(]|\([^)]*\))*\)/g, '/* date() */')
        .replace(/count\(\s*([\s\S]*?)\s*\)/g, '$1.length')
        .replace(/\$([a-zA-Z_]\w*)/g, '$1')
        .replace(/\[['"]([a-zA-Z_]\w*)['"]\]/g, '.$1')
        .trim();
}

function condToVue(s) {
    return toVue(
        s.replace(/^(?:else)?if\s*\(/, '')
         .replace(/\)\s*:\s*$/, '')
    );
}

// ─── Directive injection (second pass) ───────────────────────────────────────
// Each marker comment is removed and its directive added to the next HTML element.
// Handles single-element blocks directly; multi-element blocks need a manual
// <template v-for> / <template v-if> wrapper — left for the developer.

function injectDirectives(html) {
    // v-for
    html = injectDirective(html, 'v-for');
    html = html.replace(/[ \t]*<!--\s*end-v-for\s*-->[ \t]*/g, '');

    // v-if / v-else-if / v-else — order matters (else-if before else before end)
    html = injectDirective(html, 'v-if');
    html = injectDirective(html, 'v-else-if');
    html = injectElse(html);
    html = html.replace(/[ \t]*<!--\s*end-v-if\s*-->[ \t]*/g, '');

    return html;
}

function injectDirective(html, dir) {
    // (\s*) captures all whitespace (including lines with only spaces from stripped
    // PHP blocks) between the comment and the next element. We then extract just the
    // last line's indentation so the element lands on a clean line.
    return html.replace(
        new RegExp(`[ \\t]*<!--\\s*${dir}:\\s*(.*?)\\s*-->[ \\t]*(\\s*)<(\\w+)`, 'g'),
        (_, expr, ws, tag) => {
            const indent = indentOf(ws);
            return `\n${indent}<${tag} ${dir}="${expr}"`;
        }
    );
}

function injectElse(html) {
    return html.replace(
        /[ \t]*<!--\s*v-else\s*-->[ \t]*(\s*)<(\w+)/g,
        (_, ws, tag) => `\n${indentOf(ws)}<${tag} v-else`
    );
}

function indentOf(ws) {
    const nl = ws.lastIndexOf('\n');
    return nl >= 0 ? ws.slice(nl + 1) : ws;
}

function forEachToVue(s) {
    // foreach ($arr as $val) :  or  foreach ($arr as $key => $val) :
    // Array expression may include property access: $order['items']
    const m = s.match(/^foreach\s*\(\s*(.*?)\s+as\s+(?:\$(\w+)\s*=>\s*)?\$(\w+)\s*\)\s*:$/);
    if (m) {
        const [, arrExpr, key, val] = m;
        const arr = toVue(arrExpr);
        return key ? `(${val}, ${key}) in ${arr}` : `${val} in ${arr}`;
    }
    return s.replace(/^foreach\s*\(/, '').replace(/\)\s*:$/, '');
}
