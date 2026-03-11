<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package HerdLine
 */

namespace App;

use Timber\Timber;

$templates = array( 'templates/archive.twig', 'templates/index.twig' );

$archive_title = 'Archive';
if ( is_day() ) {
	$archive_title = 'Archive: ' . get_the_date( 'D M Y' );
} elseif ( is_month() ) {
	$archive_title = 'Archive: ' . get_the_date( 'M Y' );
} elseif ( is_year() ) {
	$archive_title = 'Archive: ' . get_the_date( 'Y' );
} elseif ( is_tag() ) {
	$archive_title = single_tag_title( '', false );
} elseif ( is_category() ) {
	$archive_title = single_cat_title( '', false );
} elseif ( is_post_type_archive() ) {
	$archive_title = post_type_archive_title( '', false );
	array_unshift( $templates, 'templates/archive-' . get_post_type() . '.twig' );
}

$context = Timber::context(
	array(
		'title' => $archive_title,
	)
);

Timber::render( $templates, $context );
