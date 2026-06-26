<?php
/**
 * Table of Contents block for herdline theme
 *
 * @package herdline
 */

use Timber\Timber;

/**
 * Pull the hero image and subheading out of a story's hero block.
 *
 * Mirrors the more-stories block: each linked page is expected to lead with an
 * acf/hero block, from which we lift the focal-point image and the dek.
 *
 * @param WP_Post $post The story post.
 * @return array{hero_image: ?\Timber\Image, subheading: ?string}
 */
function herdline_toc_story_hero( $post ) {
	$hero_image = null;
	$subheading = null;

	if ( has_blocks( $post->post_content ) ) {
		foreach ( parse_blocks( $post->post_content ) as $block_data ) {
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

	return array(
		'hero_image' => $hero_image,
		'subheading' => $subheading,
	);
}

/**
 * This is the callback that displays the block.
 *
 * @param   array  $block      The block settings and attributes.
 * @param   string $content    The block content (empty string).
 * @param   bool   $is_preview True during AJAX preview.
 */
function herdline_toc_block( $block, $content = '', $is_preview = false ) {
	$context               = Timber::context();
	$context['block']      = $block;
	$context['fields']     = get_fields() ? get_fields() : array();
	$context['is_preview'] = $is_preview;

	$context['intro_label'] = get_field( 'intro_label' );
	$context['issue_label'] = get_field( 'issue_label' );

	$sections     = array();
	$raw_sections = get_field( 'sections' );

	if ( $raw_sections ) {
		foreach ( $raw_sections as $row ) {
			$stories = array();

			if ( ! empty( $row['stories'] ) ) {
				foreach ( $row['stories'] as $post ) {
					$hero             = herdline_toc_story_hero( $post );
					$card_image_field = get_field( 'card_image', $post->ID );
					$card_image       = ! empty( $card_image_field['ID'] ) ? Timber::get_image( $card_image_field['ID'] ) : null;

					$stories[] = array(
						'title'       => get_the_title( $post->ID ),
						'url'         => get_permalink( $post->ID ),
						'id'          => $post->ID,
						'hero_image'  => $card_image ? $card_image : $hero['hero_image'],
						'subheading'  => $hero['subheading'],
						'description' => get_field( 'description', $post->ID ),
						'author'      => get_field( 'author', $post->ID ),
						'story_type'  => get_field( 'story_type', $post->ID ),
					);
				}
			}

			$sections[] = array(
				'label'      => isset( $row['section_label'] ) ? $row['section_label'] : '',
				'stories'    => $stories,
				'basic_grid' => ! empty( $row['basic_grid'] ),
			);
		}
	}

	$context['sections'] = $sections;

	$classes = array( 'herdline-block', 'wp-block-herdline-toc' );

	if ( ! empty( $block['className'] ) ) {
		$classes[] = $block['className'];
	}

	if ( ! empty( $context['fields']['background'] ) ) {
		$classes[] = 'has-' . sanitize_html_class( $context['fields']['background'] ) . '-background-color';
		$classes[] = 'has-background';
	}

	$context['block_classes'] = implode( ' ', $classes );

	$context['anchor'] = ! empty( $block['anchor'] ) ? esc_attr( $block['anchor'] ) : '';

	Timber::render( 'blocks/toc.twig', $context );
}
