<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package HerdLine
 */

namespace App;

use Timber\Timber;

$context      = Timber::context();
$current_post = $context['post'];

if ( $current_post->post_parent ) {
	$related_posts = Timber::get_posts(
		array(
			'post_type'      => 'page',
			'post_parent'    => $current_post->post_parent,
			'post__not_in'   => array( $current_post->ID ),
			'posts_per_page' => 4,
			'orderby'        => 'rand',
			'no_found_rows'  => true,
		)
	);

	$type_labels = array(
		'student'   => 'Student Spotlight',
		'staff'     => 'Staff Spotlight',
		'alum'      => 'Alumni Spotlight',
		'athletics' => 'Athletics Spotlight',
		'faculty'   => 'Faculty Feature',
		'history'   => 'Moment in Marshall History',
	);

	$parent_title = get_the_title( $current_post->post_parent );
	$related      = array();

	foreach ( $related_posts as $related_post ) {
		// The story's lead image lives in its `acf/hero` block attributes,
		// not as a featured image — mirror the Feature Stories block lookup.
		$hero_image   = null;
		$subheading   = '';
		$post_content = get_post_field( 'post_content', $related_post->ID );

		if ( has_blocks( $post_content ) ) {
			foreach ( parse_blocks( $post_content ) as $block_data ) {
				if ( 'acf/hero' === $block_data['blockName'] ) {
					$data = isset( $block_data['attrs']['data'] ) ? $block_data['attrs']['data'] : array();

					if ( ! empty( $data['hero_image'] ) ) {
						$image_obj = Timber::get_image( $data['hero_image'] );

						if ( $image_obj ) {
							$hero_image = array(
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

		$story_type = get_post_meta( $related_post->ID, 'story_type', true );

		$card_title = get_field( 'card_title', $related_post->ID );

		$related[] = array(
			'title'       => $card_title ? $card_title : get_the_title( $related_post->ID ),
			'url'         => get_permalink( $related_post->ID ),
			'id'          => $related_post->ID,
			'hero_image'  => $hero_image,
			'kicker'      => isset( $type_labels[ $story_type ] ) ? $type_labels[ $story_type ] : $parent_title,
			'author'      => get_post_meta( $related_post->ID, 'author', true ),
			'description' => get_field( 'description', $related_post->ID ),
			'subheading'  => $subheading,
		);
	}

	$context['related_stories'] = $related;
}


Timber::render( 'templates/page.twig', $context );
