# View Converter

Convert legacy PHP view templates to **Twig** or **Blade** syntax, and convert legacy JavaScript files to **Stimulus controllers**, **Vue 3 SFCs**, or modern vanilla JS.

## Installation

```bash
composer require kerrialn/view-converter
```

## Requirements

- PHP >= 7.4
- Node.js >= 16 _(required for `stimulus:convert`, `jquery:convert`, and `vue:convert`)_

---

## Commands

### `convert` — PHP templates → Twig or Blade

#### Convert a directory

```bash
php bin/view-converter convert ./views
```

You will be prompted to choose the output format:

```
Which format would you like to convert TO?
  [twig ] Twig (*.twig)
  [blade] Blade (*.blade.php)
```

#### Convert a single file

```bash
php bin/view-converter convert ./views/dashboard.php
```

#### Specify the format directly

```bash
php bin/view-converter convert ./views --format=twig
php bin/view-converter convert ./views --format=blade
```

#### Options

| Option | Description |
|---|---|
| `--format=twig\|blade` | Output format (prompted if omitted) |
| `--dry-run` | Preview the conversion without writing files |
| `--delete-originals` | Delete the original `.php` files after conversion |

---

### `stimulus:convert` — Legacy JS → Stimulus controller

Parses a vanilla JavaScript file using an AST parser (Node.js + [acorn](https://github.com/acornjs/acorn)) and generates a [Stimulus](https://stimulus.hotwired.dev/) controller scaffold. Optionally scans your HTML/Twig/Blade templates and reports exactly which `data-` attributes to add to each element.

#### Basic usage

```bash
php bin/view-converter stimulus:convert ./js/dropdown.js
```

#### With template scanning

```bash
php bin/view-converter stimulus:convert ./js/dropdown.js --html-dir=./views
```

#### Preview without writing a file

```bash
php bin/view-converter stimulus:convert ./js/dropdown.js --dry-run
```

#### Options

| Option | Description |
|---|---|
| `--html-dir=<path>` | Directory to scan for HTML/Twig/Blade templates |
| `--name=<name>` | Override the generated controller name |
| `--output=<path>` | Output directory for the generated controller file (defaults to same directory as the input) |
| `--dry-run` | Preview the generated controller without writing a file |

#### What gets detected

Given a legacy JS file, the converter identifies:

| JS pattern | Stimulus output |
|---|---|
| `document.querySelectorAll('.dropdown')` | Root element — `data-controller` candidate |
| `el.querySelector('.dropdown-toggle')` | `static targets = ["toggle"]` |
| `el.addEventListener('click', fn)` | Action method + `data-action="click->name#method"` suggestion |
| `el.dataset.selectedValue` | `static values = { selectedValue: String }` |
| Inline handler body | Migrated verbatim into the method, with TODO comments for anything that needs manual attention |
| `document.addEventListener(...)` | Warning issued — flagged as an outside-click pattern requiring manual migration via `connect()`/`disconnect()` |

#### Example output

Input: `dropdown.js`

```js
(function () {
    var dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(function (dropdown) {
        var toggle = dropdown.querySelector('.dropdown-toggle');
        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            // ...
        });
    });
})();
```

Generated: `dropdown-controller.js`

```js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = ["toggle", "menu", "item", "selected"]

  static values = {
    selectedValue: String,
  }

  connect() {
    // TODO: add any setup that runs when the controller connects to the DOM
  }

  handleToggleClick(event) {
    event.preventDefault();
    // ...
  }

  disconnect() {
    // TODO: remove any document-level listeners that were added in connect()
  }
}
```

Template scan report (when `--html-dir` is used):

```
views/nav.html
  Line 11  Add data-controller="dropdown"
  Line 12  Add data-dropdown-target="toggle"
  Line 12  Add data-action="click->dropdown#handleToggleClick"
  Line 15  Add data-dropdown-target="menu"
```

> **Note:** Node.js dependencies are installed automatically on first run inside the package's `node/` directory — no manual setup required.

---

### `jquery:convert` — Legacy jQuery JS → vanilla JS

Rewrites a jQuery-heavy script to modern vanilla JS using an AST parser. Handles the most common jQuery patterns automatically and leaves a TODO comment for anything that needs manual attention.

#### Basic usage

```bash
php bin/view-converter jquery:convert ./js/modal.js
```

#### Preview without writing a file

```bash
php bin/view-converter jquery:convert ./js/modal.js --dry-run
```

#### Convert a whole directory

```bash
php bin/view-converter jquery:convert ./js --output=./js/vanilla
```

#### Options

| Option | Description |
|---|---|
| `--output=<path>` | Output directory (defaults to same directory as input) |
| `--dry-run` | Preview the converted output without writing a file |

#### What gets converted

| jQuery | Vanilla JS |
|---|---|
| `$(selector)` / `$(el)` | `document.querySelector` / `document.querySelectorAll` |
| `$.ready` / `$(fn)` / `$(document).ready(fn)` | `document.addEventListener('DOMContentLoaded', fn)` |
| `.on('click', fn)` | `.addEventListener('click', fn)` |
| `.on('click keydown', fn)` | `['click','keydown'].forEach((ev) => el.addEventListener(ev, fn))` |
| `.addClass` / `.removeClass` / `.toggleClass` | `.classList.add` / `.classList.remove` / `.classList.toggle` |
| `.hasClass` | `.classList.contains` |
| `.attr(name)` / `.attr(name, val)` | `.getAttribute` / `.setAttribute` |
| `.val()` / `.val(v)` | `.value` / `.value = v` |
| `.text()` / `.html()` | `.textContent` / `.innerHTML` |
| `.hide()` / `.show()` | `style.display = 'none'` / `style.display = ''` |
| `.css(prop, val)` | `.style.prop = val` |
| `.find(sel)` | `.querySelector(sel)` |
| `.closest(sel)` | `.closest(sel)` |
| `.each(fn)` | `.forEach(fn)` |
| `$.ajax` / `$.post` / `$.get` / `$.getJSON` | `fetch(...)` with chained `.then()` |
| `var` declarations | `let` |
| jQuery IIFE wrapper `(function($){…})($)` | Stripped (body kept) |

Output files use a `.vanilla.js` suffix.

---

### `vue:convert` — Vanilla JS + HTML template → Vue 3 SFC

Transforms a vanilla JS file (and an optional HTML/PHP/Twig template) into a Vue 3 Single File Component skeleton. Can optionally run the jQuery converter first as part of the pipeline.

#### Basic usage

```bash
php bin/view-converter vue:convert ./js/modal.js
```

#### With an HTML/PHP template

```bash
php bin/view-converter vue:convert ./js/modal.js --html=./views/modal.php
```

#### From jQuery in one step

```bash
php bin/view-converter vue:convert ./js/modal.js --from-jquery --html=./views/modal.php
```

#### Preview without writing a file

```bash
php bin/view-converter vue:convert ./js/modal.js --html=./views/modal.php --dry-run
```

#### Options

| Option | Description |
|---|---|
| `--html=<path>` | HTML/PHP/Twig template to extract the `<template>` block from |
| `--from-jquery` | Run the jQuery converter first, then produce the Vue SFC |
| `--output=<path>` | Output directory (defaults to same directory as input) |
| `--dry-run` | Preview the generated SFC without writing a file |

#### What gets converted

**JavaScript:**

| Pattern | Vue output |
|---|---|
| `document.addEventListener('DOMContentLoaded', fn)` | `onMounted(fn)` |
| `document.getElementById('foo')` | Annotated with a `ref` TODO comment; `ref="foo"` injected in template |
| `querySelector` / `querySelectorAll` | Annotated with a `ref` TODO comment |
| Vue imports | Collected and emitted as a single `import { onMounted, ref } from 'vue'` line |

**PHP/Twig template syntax (when `--html` points to a `.php` or `.twig` file):**

| PHP | Vue template |
|---|---|
| `<?= $var ?>` / `<?php echo $var ?>` | `{{ var }}` |
| `<?php if ($x): ?>` | `v-if="x"` injected on the next element |
| `<?php elseif ($x): ?>` | `v-else-if="x"` injected on the next element |
| `<?php else: ?>` | `v-else` injected on the next element |
| `<?php foreach ($arr as $val): ?>` | `v-for="val in arr"` injected on the next element |
| `htmlspecialchars($x)` | `$x` (Vue auto-escapes) |
| `count($x)` | `x.length` |
| `$arr['key']` | `arr.key` |

> Multi-element `v-if` / `v-for` blocks (needing a `<template>` wrapper) and counter-based `for` loops are flagged with a comment for manual migration.

Output files use a `.vue` extension.

---

## What gets converted (PHP → Twig)

Uses a full AST parser to accurately convert PHP template logic.

| PHP | Twig |
|---|---|
| `<?php echo $name ?>` | `{{ name }}` |
| `<?= $items\|count ?>` | `{{ items\|length }}` |
| `<?php if ($active): ?>` | `{% if active %}` |
| `<?php foreach ($items as $item): ?>` | `{% for item in items %}` |
| `<?php echo $a . $b ?>` | `{{ a ~ b }}` |
| `isset($x)` | `x is defined` |
| `empty($x)` | `x is empty` |
| `Class::CONST` | `constant('Class::CONST')` |

## What gets converted (PHP → Blade)

Uses pattern-based conversion to produce Laravel Blade syntax.

| PHP | Blade |
|---|---|
| `<?php echo $name ?>` | `{{ $name }}` |
| `<?= $name ?>` | `{{ $name }}` |
| `<?php if ($active): ?>` | `@if($active)` |
| `<?php elseif ($x): ?>` | `@elseif($x)` |
| `<?php else: ?>` | `@else` |
| `<?php endif; ?>` | `@endif` |
| `<?php foreach ($items as $item): ?>` | `@foreach($items as $item)` |
| `<?php for ($i=0; $i<$n; $i++): ?>` | `@for($i=0; $i<$n; $i++)` |
| `<?php while ($running): ?>` | `@while($running)` |
| `<?php $x = 1; ?>` | `@php` / `@endphp` |

## License

MIT
