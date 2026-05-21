# View Converter

Convert legacy PHP view templates to **Twig** or **Blade** syntax.

## Installation

```bash
composer require kerrialn/view-converter
```

## Requirements

- PHP >= 7.4

## Usage

### Convert a directory

```bash
php bin/view-converter convert ./views
```

You will be prompted to choose the output format:

```
Which format would you like to convert TO?
  [twig ] Twig (*.twig)
  [blade] Blade (*.blade.php)
```

### Convert a single file

```bash
php bin/view-converter convert ./views/dashboard.php
```

### Specify the format directly

```bash
php bin/view-converter convert ./views --format=twig
php bin/view-converter convert ./views --format=blade
```

### Options

| Option | Description |
|---|---|
| `--format=twig\|blade` | Output format (prompted if omitted) |
| `--dry-run` | Preview the conversion without writing files |
| `--delete-originals` | Delete the original `.php` files after conversion |

### Dry run

```bash
php bin/view-converter convert ./views --format=twig --dry-run
```

## What gets converted

### PHP → Twig

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

### PHP → Blade

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
