# Marshall Magazine

WordPress site for [www.marshallmagazine.com](https://www.marshallmagazine.com), built on a Bedrock-style architecture with a custom theme called **HerdLine**.

## Stack

- **WordPress 6.9** managed via Composer (Bedrock)
- **HerdLine** — custom theme using [Timber](https://timber.github.io/docs/) (Twig templating) and ACF Pro blocks
- **Tailwind CSS v4** for styling
- **Alpine.js 3** for interactivity

## Requirements

- PHP 8.3+
- Composer
- Node.js / npm
- [Laravel Herd](https://herd.laravel.com/) or similar local server (MySQL)

## Local Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy and configure environment
cp .env.example .env
# Edit .env with your DB credentials and WP keys

# 3. Install theme dependencies and build CSS
cd web/app/themes/herdline
npm install
npm run dev
```

Local dev URL: `http://marshallmagazine.test`

## Project Structure

```
config/             # WordPress configuration (loads .env)
web/
  wp/               # WordPress core (Composer-managed, do not edit)
  app/
    mu-plugins/     # Must-use plugins
    plugins/        # Composer-managed plugins
    themes/
      herdline/     # Custom theme
```

## HerdLine Theme

The theme is located at `web/app/themes/herdline/`. Key directories:

- `src/` — Main theme class (`StarterSite`) extending `Timber\Site`
- `views/` — Twig templates (`blocks/`, `partials/`, `layouts/`, `templates/`)
- `blocks/` — Custom ACF block registrations
- `acf-json/` — Version-controlled ACF field configurations
- `source/css/` — Tailwind source; compiled output goes to `css/`

### Theme Commands

Run from `web/app/themes/herdline/`:

```bash
npm run dev         # Watch and compile Tailwind CSS
npm run build       # Production build

composer lint       # PHP CodeSniffer
composer format     # Auto-fix PHP formatting
composer twig-lint  # Validate Twig templates
```

### Generating a New Block

```bash
wp parkour make:block
```

## Deployment

PHP dependencies (plugins, WordPress core) are managed by Composer. Run `composer install --no-dev` on the server. The `web/` directory is the document root.
