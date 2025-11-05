# HerdLine

A WordPress theme built with Timber and Twig templating, styled with Tailwind CSS, and enhanced with Alpine.js for interactive components.

## Requirements

- PHP 8.0+
- WordPress 6.0+
- Composer
- Node.js and npm
- ACF Pro

## Installing the theme

Follow the guide on [how to Install Timber](https://timber.github.io/docs/v2/installation/installation/).

Then:

1. Clone or download this theme into your WordPress themes directory
2. Run `composer install` to install PHP dependencies
3. Run `npm install` to install Node dependencies
4. Activate the theme in the WordPress Dashboard under **Appearance → Themes**
5. Ensure ACF Pro is installed and activated

## Development

### CSS Development
We use Tailwind CSS v4 with the Tailwind CLI for styling.

**Development mode** (with file watching):
```bash
npm run dev
```

**Production build** (minified):
```bash
npm run build
```

Source CSS is located at `source/css/herdline.css` and compiled to `css/herdline.css`.

### JavaScript
Alpine.js is loaded via CDN for interactive components. We include several Alpine.js plugins:
- `@alpinejs/collapse`
- `@alpinejs/focus`
- `@alpinejs/intersect`

### Code Quality

#### PHP Linting
We follow WordPress Coding Standards. PHP linting is configured with PHP_CodeSniffer.

**Check code for issues:**
```bash
composer lint
```

**Auto-fix formatting issues:**
```bash
composer format
```

#### Twig Linting
Twig templates are syntax-checked with:
```bash
composer twig-lint
```

#### Git Hooks
We use Lefthook to automatically lint PHP and Twig files on commit. The pre-commit hook runs both `composer lint` and `composer twig-lint` to ensure code quality before committing.

To set up git hooks:
```bash
npx lefthook install
```

## Custom Gutenberg Blocks

Custom blocks are created using ACF Pro and managed with [Parkour](https://github.com/mccomaschris/parkour), which provides a streamlined workflow for generating block files.

## Theme Structure

- `src/` - PHP classes and theme functionality (autoloaded via Composer)
- `views/` - Twig templates that correspond to the WordPress template hierarchy
- `source/css/` - Source CSS files for Tailwind
- `css/` - Compiled CSS output

## PHP Dependencies

- **Timber** - Twig templating for WordPress
- **Carbon** - Date/time manipulation library
- **PHP_CodeSniffer & WPCS** - Code quality tools (dev)
- **ACF Pro Stubs** - IDE autocomplete support for ACF (dev)

## IDE Setup (VS Code)

The `.vscode/settings.json` file includes configuration for:
- PHP validation with Laravel Herd
- Intelephense with WordPress stubs for better autocomplete

## Autoloading

PHP classes in the `src/` directory are autoloaded via Composer's classmap functionality. There's no need to manually require files in **functions.php**.

## Resources

* [Timber Documentation](https://timber.github.io/docs/)
* [Twig for Timber Cheatsheet](https://notlaura.com/the-twig-for-timber-cheatsheet/)
* [Tailwind CSS Documentation](https://tailwindcss.com/docs)
* [Alpine.js Documentation](https://alpinejs.dev/)
* [ACF Pro Documentation](https://www.advancedcustomfields.com/resources/)
* [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
