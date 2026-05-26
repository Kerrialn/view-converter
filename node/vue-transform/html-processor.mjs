import { readFileSync } from 'fs';
import { processPhpTemplate } from './php-processor.mjs';

/**
 * Reads an HTML/Twig/PHP file, converts any PHP template syntax to Vue
 * template approximations, injects Vue ref attributes on elements whose
 * id matches a collected getElementById() call from the JS transform.
 *
 * @param {string}      htmlPath  Path to the template file (.html, .php, .twig)
 * @param {Map<string,string>} domRefs  id → camelCase ref name collected by dom-query rule
 * @returns {string}    Template content ready for <template> block
 */
export function processHtml(htmlPath, domRefs) {
    let html = readFileSync(htmlPath, 'utf8');

    if (htmlPath.endsWith('.php') || htmlPath.endsWith('.twig')) {
        html = processPhpTemplate(html);
    }

    for (const [id, refName] of domRefs) {
        // Inject ref="name" immediately after id="value" wherever it appears in a tag.
        // Handles both single and double quotes around the id value.
        html = html.replace(
            new RegExp(`\\bid=(["'])${escapeRegex(id)}\\1`, 'g'),
            `id="${id}" ref="${refName}"`
        );
    }

    return html.trimEnd();
}

function escapeRegex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
