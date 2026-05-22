/**
 * Pure transformation helpers — all accept `src` (node → string slice) as a
 * parameter so they remain stateless and testable in isolation.
 */
import { isJqueryCall, isFn } from './context.mjs';

// ─── Selector extraction ──────────────────────────────────────────────────────

export function extractSelector(jqNode, src) {
    if (!isJqueryCall(jqNode) || jqNode.arguments.length !== 1) return null;

    const arg = jqNode.arguments[0];

    if (arg.type === 'Literal' && typeof arg.value === 'string') {
        const sel = arg.value;
        if (/^#[\w-]+$/.test(sel))  return { expr: `document.getElementById('${sel.slice(1)}')`, isMultiple: false };
        if (sel === 'body')          return { expr: 'document.body',            isMultiple: false };
        if (sel === 'html')          return { expr: 'document.documentElement', isMultiple: false };
        if (sel === 'window')        return { expr: 'window',                   isMultiple: false };
        return { expr: `document.querySelectorAll(${JSON.stringify(sel)})`, isMultiple: true };
    }

    if (arg.type === 'ThisExpression') return { expr: 'this',     isMultiple: false };
    if (arg.type === 'Identifier') {
        if (arg.name === 'document') return { expr: 'document',  isMultiple: false };
        if (arg.name === 'window')   return { expr: 'window',    isMultiple: false };
        return { expr: arg.name, isMultiple: false };
    }

    return { expr: `document.querySelector(${src(arg)})`, isMultiple: false };
}

// ─── Method body template (single-element, EL placeholder) ───────────────────

export function methodBody(method, args, src) {
    const a   = i => i < args.length ? src(args[i]) : 'undefined';
    const lit = i => (args[i] && args[i].type === 'Literal') ? args[i].value : null;
    switch (method) {
        case 'show':        return `EL.style.display = ''`;
        case 'hide':        return `EL.style.display = 'none'`;
        case 'text':        return args.length > 0 ? `EL.textContent = ${a(0)}` : null;
        case 'html':        return args.length > 0 ? `EL.innerHTML = ${a(0)}`   : null;
        case 'addClass':    return `EL.classList.add(${a(0)})`;
        case 'removeClass': return `EL.classList.remove(${a(0)})`;
        case 'toggleClass': return `EL.classList.toggle(${a(0)})`;
        case 'val':         return args.length > 0 ? `EL.value = ${a(0)}` : null;
        case 'attr':        return args.length >= 2 ? `EL.setAttribute(${a(0)}, ${a(1)})` : null;
        case 'removeAttr':  return `EL.removeAttribute(${a(0)})`;
        case 'remove':      return `EL.remove()`;
        case 'empty':       return `EL.innerHTML = ''`;
        case 'css': {
            const camel = lit(0) ? toCamelCase(lit(0)) : null;
            return args.length >= 2
                ? (camel ? `EL.style.${camel} = ${a(1)}` : `EL.style[${a(0)}] = ${a(1)}`)
                : null;
        }
        default: return null;
    }
}

// ─── Full method transformer (handles apply/getter pattern) ───────────────────

export function transformMethod(method, { expr, isMultiple }, args, src) {
    const apply  = tpl => isMultiple
        ? `${expr}.forEach((el) => ${tpl.replace(/\bEL\b/g, 'el')})`
        : tpl.replace(/\bEL\b/g, expr);
    const getter = tpl => isMultiple
        ? `/* TODO: reading from multiple elements — only first returned */ ${tpl.replace(/\bEL\b/g, `${expr}[0]`)}`
        : tpl.replace(/\bEL\b/g, expr);

    const a   = i => i < args.length ? src(args[i]) : 'undefined';
    const lit = i => (args[i] && args[i].type === 'Literal') ? args[i].value : null;

    switch (method) {
        case 'addClass':    return apply(`EL.classList.add(${a(0)})`);
        case 'removeClass': return apply(`EL.classList.remove(${a(0)})`);
        case 'toggleClass': return apply(`EL.classList.toggle(${a(0)})`);
        case 'hasClass':    return getter(`EL.classList.contains(${a(0)})`);
        case 'on':
        case 'off':         return null; // only reached without fn arg
        case 'trigger':     return apply(`EL.dispatchEvent(new Event(${a(0)}, { bubbles: true }))`);
        case 'attr':        return args.length >= 2 ? apply(`EL.setAttribute(${a(0)}, ${a(1)})`) : getter(`EL.getAttribute(${a(0)})`);
        case 'removeAttr':  return apply(`EL.removeAttribute(${a(0)})`);
        case 'prop': {
            const p = lit(0);
            return args.length >= 2
                ? apply(p ? `EL.${p} = ${a(1)}` : `EL[${a(0)}] = ${a(1)}`)
                : getter(p ? `EL.${p}` : `EL[${a(0)}]`);
        }
        case 'val':    return args.length > 0 ? apply(`EL.value = ${a(0)}`)       : getter(`EL.value`);
        case 'text':   return args.length > 0 ? apply(`EL.textContent = ${a(0)}`) : getter(`EL.textContent`);
        case 'html':   return args.length > 0 ? apply(`EL.innerHTML = ${a(0)}`)   : getter(`EL.innerHTML`);
        case 'show':   return apply(`EL.style.display = ''`);
        case 'hide':   return apply(`EL.style.display = 'none'`);
        case 'toggle': return args.length === 0
            ? apply(`EL.style.display = EL.style.display === 'none' ? '' : 'none'`)
            : null;
        case 'css': {
            const camel = lit(0) ? toCamelCase(lit(0)) : null;
            return args.length >= 2
                ? apply(camel ? `EL.style.${camel} = ${a(1)}` : `EL.style[${a(0)}] = ${a(1)}`)
                : getter(camel ? `getComputedStyle(EL).${camel}` : `getComputedStyle(EL)[${a(0)}]`);
        }
        case 'data': {
            const camel = lit(0) ? toCamelCase(lit(0)) : null;
            return args.length >= 2
                ? apply(camel ? `EL.dataset.${camel} = ${a(1)}` : `EL.dataset[${a(0)}] = ${a(1)}`)
                : getter(camel ? `EL.dataset.${camel}` : `EL.dataset[${a(0)}]`);
        }
        case 'find':     return getter(`EL.querySelectorAll(${a(0)})`);
        case 'closest':  return getter(`EL.closest(${a(0)})`);
        case 'parent':   return getter(`EL.parentElement`);
        case 'children': return getter(`EL.children`);
        case 'next':     return getter(`EL.nextElementSibling`);
        case 'prev': case 'previous': return getter(`EL.previousElementSibling`);
        case 'first':    return isMultiple ? `${expr}[0]` : getter(`EL`);
        case 'last':     return isMultiple ? `Array.from(${expr}).at(-1)` : getter(`EL`);
        case 'index':    return getter(`Array.from(EL.parentElement.children).indexOf(EL)`);
        case 'siblings': return getter(`Array.from(EL.parentElement.children).filter((c) => c !== EL)`);
        case 'remove':   return apply(`EL.remove()`);
        case 'empty':    return apply(`EL.innerHTML = ''`);
        case 'append':   return apply(`EL.append(${a(0)})`);
        case 'prepend':  return apply(`EL.prepend(${a(0)})`);
        case 'before':   return apply(`EL.before(${a(0)})`);
        case 'after':    return apply(`EL.after(${a(0)})`);
        case 'clone':    return getter(`EL.cloneNode(true)`);
        case 'detach':   return apply(`EL.remove()`);
        case 'width':    return args.length > 0 ? apply(`EL.style.width = ${a(0)}`)  : getter(`EL.getBoundingClientRect().width`);
        case 'height':   return args.length > 0 ? apply(`EL.style.height = ${a(0)}`) : getter(`EL.getBoundingClientRect().height`);
        default:         return null;
    }
}

// ─── Traversal resolution (for two-level chains) ──────────────────────────────

export function resolveTraversal(method, a, baseExpr, baseIsMultiple) {
    if (baseIsMultiple) return null;
    switch (method) {
        case 'closest':  return { expr: `${baseExpr}.closest(${a(0)})`,         isMultiple: false };
        case 'parent':   return { expr: `${baseExpr}.parentElement`,             isMultiple: false };
        case 'next':     return { expr: `${baseExpr}.nextElementSibling`,        isMultiple: false };
        case 'prev': case 'previous': return { expr: `${baseExpr}.previousElementSibling`, isMultiple: false };
        case 'find':     return { expr: `${baseExpr}.querySelectorAll(${a(0)})`, isMultiple: true  };
        case 'children': return { expr: `${baseExpr}.children`,                  isMultiple: true  };
        default:         return null;
    }
}

// ─── Partial replacement builders ────────────────────────────────────────────

export const EVENT_SHORTCUTS = [
    'click', 'submit', 'change', 'input', 'focus', 'blur',
    'keyup', 'keydown', 'mouseenter', 'mouseleave', 'scroll', 'resize',
];

export function buildPartialReplacement(method, selInfo, args, src) {
    const { expr, isMultiple } = selInfo;
    const a    = i => i < args.length ? src(args[i]) : '';
    const fnArg = args.find(isFn) ?? null;
    if (fnArg === null) return null;

    if (method === 'on' || method === 'off') {
        const handler  = method === 'on' ? 'addEventListener' : 'removeEventListener';
        const eventArg = args[0];
        const eventStr = (eventArg?.type === 'Literal' && typeof eventArg.value === 'string') ? eventArg.value : null;
        const events   = eventStr ? eventStr.trim().split(/\s+/).filter(Boolean) : null;

        if (events && events.length > 1) {
            const evList = JSON.stringify(events);
            return isMultiple
                ? { fnArg, prefix: `${expr}.forEach((el) => ${evList}.forEach((ev) => el.${handler}(ev, `, suffix: `)))` }
                : { fnArg, prefix: `${evList}.forEach((ev) => ${expr}.${handler}(ev, `,                    suffix: `))` };
        }

        return isMultiple
            ? { fnArg, prefix: `${expr}.forEach((el) => el.${handler}(${a(0)}, `, suffix: `))` }
            : { fnArg, prefix: `${expr}.${handler}(${a(0)}, `,                    suffix: null };
    }

    if (EVENT_SHORTCUTS.includes(method)) {
        return isMultiple
            ? { fnArg, prefix: `${expr}.forEach((el) => el.addEventListener('${method}', `, suffix: `))` }
            : { fnArg, prefix: `${expr}.addEventListener('${method}', `,                    suffix: null };
    }

    if (method === 'each') {
        return isMultiple
            ? { fnArg, prefix: `${expr}.forEach((el, i) => (`,    suffix: `).call(el, i, el))` }
            : { fnArg, prefix: `[${expr}].forEach((el, i) => (`,  suffix: `).call(el, i, el))` };
    }

    return null;
}

export function buildStaticPartial(method, args, fnArg, src) {
    const nonFn = args.filter(a => !isFn(a));
    const a     = i => i < nonFn.length ? src(nonFn[i]) : 'undefined';
    switch (method) {
        case 'each':    return { prefix: `${a(0)}.forEach((v, i) => (`, suffix: `).call(v, i, v))` };
        case 'get':     return { prefix: `fetch(${a(0)}).then((r) => r.text()).then(`,  suffix: `)` };
        case 'getJSON': return { prefix: `fetch(${a(0)}).then((r) => r.json()).then(`,  suffix: `)` };
        case 'post': {
            const body = nonFn.length > 1 ? `, { method: 'POST', body: JSON.stringify(${a(1)}) }` : '';
            return { prefix: `fetch(${a(0)}${body}).then((r) => r.text()).then(`, suffix: `)` };
        }
        default: return null;
    }
}

// ─── Utility ─────────────────────────────────────────────────────────────────

export function toCamelCase(str) {
    return str.replace(/-([a-z])/g, (_, c) => c.toUpperCase());
}
