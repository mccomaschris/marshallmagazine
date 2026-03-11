<?php
/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 * @link https://github.com/timber/starter-theme'
 *
 * @package HerdLine
 */

namespace App;

use Timber\Timber;

// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once get_template_directory() . '/inc/class-statamic-importer.php';
}

Timber::init();

new StarterSite();
