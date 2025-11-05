<?php
/**
 * More Stories block for herdline theme.
 *
 * @package herdline
 */

use Timber\Timber;

/**
 * Render callback for the More Stories block.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block content (empty string).
 * @param bool   $is_preview True during AJAX preview.
 */
function herdline_more_stories_block( $block, $content = '', $is_preview = false ) {
	$context               = Timber::context();
	$context['block']      = $block;
	$context['fields']     = get_fields() ? get_fields() : array();
	$context['is_preview'] = $is_preview;

	$stories_posts = get_field( 'stories' );
	$stories       = array();

	if ( $stories_posts ) {
		foreach ( $stories_posts as $post ) {
			$hero_image = null;
			$subheading = null;

			if ( has_blocks( $post->post_content ) ) {
				$blocks = parse_blocks( $post->post_content );

				foreach ( $blocks as $block_data ) {
					if ( 'acf/hero' === $block_data['blockName'] ) {
						$data = isset( $block_data['attrs']['data'] ) ? $block_data['attrs']['data'] : array();

						if ( ! empty( $data['hero_image'] ) ) {
							$image_obj = Timber::get_image( $data['hero_image'] );

							if ( $image_obj ) {
								$hero_image = $image_obj;
							}
						}

						$subheading = isset( $data['subheading'] ) ? $data['subheading'] : '';
						break;
					}
				}
			}

			$story_type = get_field( 'story_type', $post->ID );

			$stories[] = array(
				'title'      => get_the_title( $post->ID ),
				'url'        => get_permalink( $post->ID ),
				'id'         => $post->ID,
				'hero_image' => $hero_image,
				'subheading' => $subheading,
				'story_type' => $story_type,
			);
		}
	}

	$context['stories'] = $stories;

	$classes = array( 'herdline-block', 'wp-block-herdline-more-stories' );

	if ( ! empty( $block['className'] ) ) {
		$classes[] = $block['className'];
	}

	if ( ! empty( $context['fields']['background'] ) ) {
		$classes[] = 'has-' . sanitize_html_class( $context['fields']['background'] ) . '-background-color';
		$classes[] = 'has-background';
	}

	$context['block_classes'] = implode( ' ', $classes );

	$context['anchor'] = ! empty( $block['anchor'] ) ? esc_attr( $block['anchor'] ) : '';

	Timber::render( 'blocks/more-stories.twig', $context );
}
