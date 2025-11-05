<?php
/**
 * Hero block for herdline theme
 *
 * @package herdline
 */

use Timber\Timber;

/**
 * This is the callback that displays the block.
 *
 * @param   array  $block      The block settings and attributes.
 * @param   string $content    The block content (empty string).
 * @param   bool   $is_preview True during AJAX preview.
 */
function herdline_hero_block( $block, $content = '', $is_preview = false ) {
	$context               = Timber::context();
	$context['block']      = $block;
	$context['fields']     = get_fields() ? get_fields() : array();
	$context['is_preview'] = $is_preview;

	$classes = array( 'herdline-block', 'wp-block-herdline-hero' );

	if ( ! empty( $block['className'] ) ) {
		$classes[] = $block['className'];
	}

	if ( ! empty( $context['fields']['background'] ) ) {
		$classes[] = 'has-' . $context['fields']['background'] . '-background-color';
		$classes[] = 'has-background';
	}

	$context['block_classes'] = implode( ' ', $classes );

	$context['anchor'] = ! empty( $block['anchor'] ) ? esc_attr( $block['anchor'] ) : '';

	Timber::render( 'blocks/hero.twig', $context );
}
