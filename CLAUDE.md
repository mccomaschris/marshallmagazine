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
- `functions.php` — bootstraps the theme, instantiates `StarterSite`
- `src/class-startersite.php` — main theme class extending `Timber\Site`; registers blocks, menus, image sizes, etc.
- `views/` — Twig templates organized into `blocks/`, `partials/`, `layouts/`, `templates/`
- `blocks/` — custom ACF block definitions (each block has a PHP registration file + Twig view)
- `acf-json/` — ACF field group JSON (version-controlled; sync via ACF UI)
- `source/css/` — Tailwind source CSS
- `css/` — compiled output (committed to repo)

### Key Technology Choices
- **Timber 2.x** — PHP/Twig bridge; all templates rendered via `Timber::render()`
- **ACF Pro** — custom blocks use `acf_register_block_type()`; fields defined in `acf-json/`
- **Tailwind CSS v4** — utility-first CSS; config in `source/css/`
- **Alpine.js 3** — inline JS interactivity (loaded via CDN in theme header)

### Custom Blocks Pattern
Each block in `blocks/<block-name>/` typically contains:
- A PHP file that calls `acf_register_block_type()` and renders via a Twig view
- The corresponding Twig template lives in `views/blocks/<block-name>.twig`
- ACF field configuration stored in `acf-json/`

### Composer-Managed Plugins
Plugins are installed via Composer using wpackagist. Custom/VCS packages:
- `marshallu/mu-auth` — custom authentication (GitHub)
- `hirasso/focal-point-picker` — image focal point (GitHub)
- `mccomaschris/parkour` — WP-CLI block scaffolding (GitHub)

## Git Hooks
`lefthook.yml` runs pre-commit linting (PHP + Twig) from the theme directory. Install with `npx lefthook install` in the repo root.
