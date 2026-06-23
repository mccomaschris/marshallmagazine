# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Marshall Magazine is a WordPress site using a Bedrock-style architecture (Composer-managed). The custom theme is called **HerdLine**, located at `web/app/themes/herdline/`. The theme uses Timber (Twig templating) and ACF Pro for custom Gutenberg blocks.

- Local dev URL: `http://marshallmagazine.test`
- WordPress root: `web/` (document root), core installed at `web/wp/`
- WordPress content: `web/app/` (replaces `wp-content/`)

## Commands

### Root (PHP dependencies)
```bash
composer install        # Install PHP dependencies
```

### Theme (run from web/app/themes/herdline/)
```bash
npm run dev             # Watch and compile Tailwind CSS v4
npm run build           # Production build (minified)

composer lint           # PHP CodeSniffer check
composer format         # Auto-fix PHP formatting
composer twig-lint      # Validate Twig templates
```

### WP-CLI
```bash
wp parkour make:block   # Generate a new ACF block scaffold
```

## Architecture

### Bedrock Structure
- `config/application.php` — main WordPress config (loads .env)
- `config/environments/` — environment overrides (development.php enables debug/file editing)
- `web/wp/` — WordPress core (managed by Composer, do not edit)
- `web/app/` — content directory (themes, plugins, uploads)
- `web/app/mu-plugins/` — must-use plugins including bedrock-autoloader

### HerdLine Theme
The theme follows an OOP structure with Timber:
- `functions.php` — bootstraps the theme: loads Composer autoload, calls `Timber::init()`, instantiates `HerdLineSite`
- `src/class-herdlinesite.php` — `HerdLineSite`, the main theme class extending `Timber\Site`. Wires up all theme hooks: block registration, nav menus, Twig context/filters/functions, allowed block types, and the default page template. `register_post_types()` / `register_taxonomies()` are empty stubs ready for use.
- `views/` — Twig templates organized into `blocks/`, `partials/`, `layouts/`, `templates/`
- `blocks/` — custom ACF block definitions (see Custom Blocks Pattern below)
- `acf-json/` — ACF field group JSON (version-controlled; sync via ACF UI)
- `source/css/` — Tailwind source CSS
- `css/` — compiled output (committed to repo)
- `BLOCKS.md` — reference documenting every block's fields and behavior; keep it in sync when adding/changing blocks

### Key Technology Choices
- **Timber 2.x** — PHP/Twig bridge; all templates rendered via `Timber::render()`. Custom Twig filters registered in `HerdLineSite`: `focal_point` (returns CSS `object-position` from the focal-point-picker plugin) and `safe_resize`. Custom Twig function: `get_theme_mod`.
- **ACF Pro** — powers custom blocks; field groups defined in `acf-json/`
- **Tailwind CSS v4** — utility-first CSS via the Tailwind CLI; entry at `source/css/herdline.css`, compiled to `css/herdline.css`
- **Alpine.js 3** — inline JS interactivity (plus `@alpinejs/collapse`, `/focus`, `/intersect` plugins)

### Custom Blocks Pattern
Blocks are **auto-discovered**, not hand-registered. `HerdLineSite::herdline_register_blocks()` (hooked on `acf/init`) scans `blocks/`, and for each subdirectory registers `block.json` via `register_block_type()` and requires `callback.php`. Each block in `blocks/<block-name>/` contains:
- `block.json` — block metadata; `name` is `acf/<block-name>`, `category` is the custom `herdline` category, and `acf.renderCallback` names the render function
- `callback.php` — defines that render function (e.g. `herdline_<name>_block`), which builds the Timber context (`get_fields()`, block classes, anchor) and calls `Timber::render( 'blocks/<name>.twig', $context )`
- The Twig template lives in `views/blocks/<block-name>.twig`
- ACF field configuration is stored in `acf-json/`

**Gotcha:** the editor's block palette is restricted by the `allowed_block_types_all` filter in `HerdLineSite::herdline_allowed_block_types()`. After scaffolding a new block you **must** add its `acf/<name>` to that array, or it won't appear in the editor. Pages also default to a locked `acf/hero` block via `herdline_default_page_template()`.

Scaffold new blocks with `wp parkour make:block` (the `mccomaschris/parkour` WP-CLI package).

### Composer-Managed Plugins
Plugins are installed via Composer using wpackagist. Custom/VCS packages:
- `marshallu/mu-auth` — custom authentication (GitHub)
- `hirasso/focal-point-picker` — image focal point (GitHub)
- `mccomaschris/parkour` — WP-CLI block scaffolding (GitHub)

## Git Hooks
`lefthook.yml` runs pre-commit linting (PHP + Twig) from the theme directory. Install with `npx lefthook install` in the repo root.
