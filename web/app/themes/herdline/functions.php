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

Timber::init();

new HerdLineSite();
