<?php
/**
 * Feature Stories block for herdline theme.
 *
 * @package herdline
 */

use Timber\Timber;

/**
 * Render callback for the Feature Stories block.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block content (empty string).
 * @param bool   $is_preview True during AJAX preview.
 */
function herdline_feature_stories_block( $block, $content = '', $is_preview = false ) {
	$context               = Timber::context();
	$context['block']      = $block;
	$context['fields']     = get_fields() ? get_fields() : array();
	$context['is_preview'] = $is_preview;

	$stories_posts = get_field( 'stories' );
	$stories       = array();

	if ( $stories_posts ) {
		foreach ( $stories_posts as $post ) {
			$hero_background = null;
			$subheading      = null;

			if ( has_blocks( $post->post_content ) ) {
				$blocks = parse_blocks( $post->post_content );

				foreach ( $blocks as $block_data ) {
					if ( 'acf/hero' === $block_data['blockName'] ) {
						$data = isset( $block_data['attrs']['data'] ) ? $block_data['attrs']['data'] : array();

						if ( ! empty( $data['hero_image'] ) ) {
							$image_obj = Timber::get_image( $data['hero_image'] );

							if ( $image_obj ) {
								$hero_background = array(
									'id'  => $image_obj->ID,
									'url' => $image_obj->src(),
								);
							}
						}

						$subheading = isset( $data['subheading'] ) ? $data['subheading'] : '';
						break;
					}
				}
			}

			$stories[] = array(
				'title'            => get_the_title( $post->ID ),
				'url'              => get_permalink( $post->ID ),
				'id'               => $post->ID,
				'background_image' => $hero_background,
				'subheading'       => $subheading,
			);
		}
	}

	$context['stories'] = $stories;

	$classes = array( 'herdline-block', 'wp-block-herdline-feature-stories' );

	if ( ! empty( $block['className'] ) ) {
		$classes[] = $block['className'];
	}

	if ( ! empty( $context['fields']['background'] ) ) {
		$classes[] = 'has-' . sanitize_html_class( $context['fields']['background'] ) . '-background-color';
		$classes[] = 'has-background';
	}

	$context['block_classes'] = implode( ' ', $classes );

	$context['anchor'] = ! empty( $block['anchor'] ) ? esc_attr( $block['anchor'] ) : '';

	Timber::render( 'blocks/feature-stories.twig', $context );
}
