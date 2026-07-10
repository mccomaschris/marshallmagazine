<?php
/**
 * Recent Issues block for herdline theme
 *
 * @package herdline
 */

/**
 * This is the callback that displays the block.
 *
 * @param   array  $block      The block settings and attributes.
 * @param   string $content    The block content (empty string).
 * @param   bool   $is_preview True during AJAX preview.
 */
function herdline_recent_issues_block( $block, $content = '', $is_preview = false ) {
	$context               = Timber::context();
	$context['block']      = $block;
	$context['fields']     = get_fields() ? get_fields() : array();
	$context['is_preview'] = $is_preview;

	// Sort issues newest-first by their Issue Date (stored as Ymd).
	// Rows without a date sink to the bottom, keeping their relative order.
	if ( ! empty( $context['fields']['recent_issues'] ) && is_array( $context['fields']['recent_issues'] ) ) {
		usort(
			$context['fields']['recent_issues'],
			function ( $a, $b ) {
				$date_a = isset( $a['issue_date'] ) ? (string) $a['issue_date'] : '';
				$date_b = isset( $b['issue_date'] ) ? (string) $b['issue_date'] : '';
				return strcmp( $date_b, $date_a );
			}
		);
	}

	$classes = array( 'herdline-block', 'wp-block-herdline-recent-issues' );

	if ( ! empty( $block['className'] ) ) {
		$classes[] = $block['className'];
	}

	if ( ! empty( $context['fields']['background'] ) ) {
		$classes[] = 'has-' . $context['fields']['background'] . '-background-color';
		$classes[] = 'has-background';
	}

	$context['block_classes'] = implode( ' ', $classes );

	$context['anchor'] = ! empty( $block['anchor'] ) ? esc_attr( $block['anchor'] ) : '';

	Timber::render( 'blocks/recent-issues.twig', $context );
}
