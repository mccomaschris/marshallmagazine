# Repository Guidelines

## Project Structure & Module Organization

This repository is the `HerdLine` WordPress theme for Marshall Magazine. PHP theme bootstrap files live at the theme root, including `functions.php`, `index.php`, `page.php`, `single.php`, and archive/search templates. Theme functionality is in `src/`, autoloaded by Composer. Twig templates are organized under `views/`, with layouts in `views/layouts/`, page templates in `views/templates/`, partials in `views/partials/`, and block views in `views/blocks/`.

Custom ACF Gutenberg blocks live in `blocks/<block-name>/` with a `block.json` and `callback.php`; synced ACF field groups live in `acf-json/`. Source CSS is `source/css/herdline.css`, compiled output is `css/herdline.css`. Static assets are in `assets/`, with fonts in `fonts/`.

## Build, Test, and Development Commands

- `composer install`: install PHP dependencies such as Timber and Parkour.
- `npm install`: install Tailwind, Alpine-related packages, and Lefthook.
- `npm run dev`: watch `source/css/herdline.css` and rebuild `css/herdline.css`.
- `npm run build`: create the minified production CSS bundle.
- `composer lint`: run PHP_CodeSniffer with WordPress Coding Standards.
- `composer format`: auto-fix PHP formatting issues where possible.
- `composer twig-lint`: syntax-check Twig templates under `views/`.
- `npx lefthook install`: install local Git hooks.

## Coding Style & Naming Conventions

Follow WordPress Coding Standards for PHP. Use tabs for PHP indentation, snake_case for PHP functions and variables, and namespaced classes under `App\` in `src/`. Keep block folders lowercase and hyphenated, matching block slugs such as `basic-content`, `feature-stories`, and `recent-issues`.

Twig should stay presentation-focused. Put data preparation in block callbacks or PHP classes, then render in `views/`. Tailwind utility classes are the primary styling approach; edit source CSS, then rebuild compiled CSS.

## Testing Guidelines

There is no dedicated unit test suite in this theme. Before submitting changes, run `composer lint`, `composer twig-lint`, and `npm run build`. For visual or block changes, verify the affected WordPress page or post in a browser, especially responsive hero, gallery, and article layouts.

## Commit & Pull Request Guidelines

Recent commits use short, imperative subject lines, for example `Add Separator block` or `Set body sans back to Mundial`. Keep commits focused and describe the user-facing or code-level change.

Pull requests should include a concise summary, validation commands run, linked issue or task context when available, and screenshots for visual changes. Note any ACF JSON changes and whether compiled CSS was rebuilt.

## Agent-Specific Instructions

Do not overwrite generated or user-managed files without checking context. Preserve existing ACF JSON, compiled CSS, and vendor/build artifacts unless the task requires updating them.
