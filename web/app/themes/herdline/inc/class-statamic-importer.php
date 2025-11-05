<?php
/**
 * WP-CLI command to import Statamic entries into WordPress.
 *
 * @package herdline
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

class Statamic_Importer_Command {

	/**
	 * Cached image IDs keyed by filename.
	 *
	 * @var array
	 */
	private $image_cache = array();

	/**
	 * ACF field key mappings.
	 *
	 * @var array
	 */
	private $field_keys = array();

	/**
	 * Map of imported Statamic IDs to WordPress post IDs.
	 *
	 * @var array
	 */
	private $imported_map = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_field_keys();
	}

	/**
	 * Initialize ACF field key mappings.
	 */
	private function init_field_keys() {
		$this->field_keys['hero'] = array(
			'hero_image'      => 'field_68efabf2464bb',
			'photo_credit'    => 'field_68efac13464bc',
			'hide_post_title' => 'field_68efac1c464bd',
			'preheading'      => 'field_690b555a2c343',
			'subheading'      => 'field_68efc9e0f20ec',
		);

		$this->field_keys['basic-content'] = array(
			'background_style'                    => 'field_68efaaa732d57',
			'background_color'                    => 'field_68efaa6d32d56',
			'background_image'                    => 'field_68efaad432d58',
			'background_image_opacity'            => 'field_68efaaf732d59',
			'pattern'                             => 'field_68efab1a32d5a',
			'content_side'                        => 'field_68efab69e4999',
			'side_fixed_images'                   => 'field_68efab8975fd1',
			'side_fixed_images_image'             => 'field_68efab9875fd2',
			'graphic_side_image'                  => 'field_68efabc77a4cc',
			'block_content'                       => 'field_68efabd97a4cd',
			'block_content_text_content'          => 'field_68efac0112346',
			'block_content_graphics_graphic'      => 'field_68efac0312348',
			'block_content_note_title'            => 'field_68efac051234a',
			'block_content_note_copy'             => 'field_68efac061234b',
			'block_content_graphics_graphic_type' => 'field_68efac0312348',
		);

		$this->field_keys['quote'] = array(
			'quote_placement'          => 'field_68efadc4e1216',
			'quote'                    => 'field_68efade2e1217',
			'attribution'              => 'field_68efadf2e1218',
			'sub_attribution'          => 'field_68efadfae1219',
			'background'               => 'field_68efae04f6667',
			'background_color'         => 'field_68efae17a22f8',
			'background_image'         => 'field_68efae392f111',
			'quote_vertical_placement' => 'field_68efc5428efe2',
		);

		$this->field_keys['photos'] = array(
			'display_type'                 => 'field_68efacf5e2372',
			'fade_from_grayscale_to_color' => 'field_68efada7e3df9',
			'photos'                       => 'field_68efad35e2373',
			'photos_photo'                 => 'field_68efad3ee2374',
			'photos_cover_text'            => 'field_68efad50e2375',
			'photos_text_placement'        => 'field_68efad61e2377',
			'photos_photo_credit'          => 'field_68efad57e2376',
		);

		$this->field_keys['recent-issues'] = array(
			'recent_issues'             => 'field_68efae74dcac9',
			'recent_issues_link'        => 'field_68efae8bdcaca',
			'recent_issues_cover_image' => 'field_68efae96dcacb',
		);

		$this->field_keys['feature-stories'] = array(
			'stories' => 'field_68efaf0b3ccff',
		);

		$this->field_keys['more-stories'] = array(
			'stories' => 'field_68efac30f4ff9',
		);

		$this->field_keys['page_meta'] = array(
			'story_type'                => 'field_68efc742c1e58',
			'author'                    => 'field_68efc769c1e59',
			'edition_homepage'          => 'field_68efc780c1e5a',
			'hide_link_back_to_edition' => 'field_68efc78cc1e5b',
		);
	}

	/**
	 * Import Statamic entries to WordPress.
	 *
	 * ## OPTIONS
	 *
	 * [--file=<file>]
	 * : Path to the JSON export file.
	 * ---
	 * default: statamic-export.json
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp statamic import --file=statamic-export.json
	 *
	 * @when after_wp_load
	 *
	 * @param array $args       Command arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function import( $args, $assoc_args ) {
		$file = $assoc_args['file'] ?? 'statamic-export.json';

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "File not found: {$file}" );
			return;
		}

		$entries = json_decode( file_get_contents( $file ), true ); // phpcs:ignore

		if ( ! is_array( $entries ) ) {
			WP_CLI::error( 'Invalid JSON file' );
			return;
		}

		WP_CLI::line( 'Found ' . count( $entries ) . ' entries to import' );

		// --- Pass 1: Create all posts without parents ---
		$progress = \WP_CLI\Utils\make_progress_bar( 'Creating posts', count( $entries ) );
		foreach ( $entries as $entry ) {
			$post_id = $this->import_entry( $entry );
			if ( $post_id ) {
				$this->imported_map[ $entry['id'] ] = $post_id;
			}
			$progress->tick();
		}
		$progress->finish();

		// --- Pass 2: Assign parent relationships ---
		WP_CLI::line( 'Assigning parent relationships...' );
		$this->assign_parent_relationships( $entries );

		WP_CLI::success( sprintf( 'Imported %d entries', count( $entries ) ) );
	}

	/**
	 * Check which images are missing from the Media Library
	 *
	 * ## OPTIONS
	 *
	 * [--file=<file>]
	 * : Path to the JSON export file
	 * ---
	 * default: statamic-export.json
	 * ---
	 *
	 * [--show-found]
	 * : Show all found image mappings
	 *
	 * ## EXAMPLES
	 *
	 *     wp statamic check-images --file=statamic-export.json
	 *     wp statamic check-images --file=statamic-export.json --show-found
	 *
	 * @param array $args       Command arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function check_images( $args, $assoc_args ) {
		$file       = $assoc_args['file'] ?? 'statamic-export.json';
		$show_found = isset( $assoc_args['show-found'] ) ? (bool) $assoc_args['show-found'] : false;

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "File not found: {$file}" );
			return;
		}

		$entries    = json_decode( file_get_contents( $file ), true ); // phpcs:ignore
		$all_images = array();

		foreach ( $entries as $entry ) {
			if ( ! empty( $entry['hero_image'] ) ) {
				$img          = is_array( $entry['hero_image'] ) ? $entry['hero_image'][0] : $entry['hero_image'];
				$all_images[] = str_replace( 'assets::', '', $img );
			}
			if ( ! empty( $entry['content'] ) ) {
				$this->collect_images_from_blocks( $entry['content'], $all_images );
			}
		}

		$all_images     = array_values( array_unique( $all_images ) );
		$missing_images = array();
		$found_map      = array();

		WP_CLI::line( 'Checking ' . count( $all_images ) . ' images...' );

		foreach ( $all_images as $filename ) {
			$attachment_id = $this->find_attachment_id_by_filename( $filename );
			if ( $attachment_id ) {
				if ( $show_found ) {
					$file_field = get_post_meta( $attachment_id, '_wp_attached_file', true );

					$found_map[ $filename ] = array(
						'id'   => $attachment_id,
						'file' => $file_field,
						'url'  => wp_get_attachment_url( $attachment_id ),
					);
				}
			} else {
				$missing_images[] = $filename;
			}
		}

		if ( empty( $missing_images ) ) {
			WP_CLI::success( 'All images found in Media Library!' );
		} else {
			WP_CLI::warning( count( $missing_images ) . ' images missing from Media Library:' );
			foreach ( $missing_images as $missing ) {
				WP_CLI::line( "  - {$missing}" );
			}
		}

		if ( $show_found && ! empty( $found_map ) ) {
			WP_CLI::line( '' );
			WP_CLI::line( 'Found mappings:' );
			foreach ( $found_map as $src => $info ) {
				WP_CLI::line( "  - {$src}  =>  #{$info['id']}  {$info['file']}" );
			}
		}
	}

	/**
	 * Recursively collect image filenames from blocks
	 *
	 * @param array $blocks Blocks data.
	 * @param array $images Collected images.
	 * @return void
	 */
	private function collect_images_from_blocks( $blocks, &$images ) {
		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			$image_fields = array( 'background_image', 'side_fixed_image', 'photo', 'cover_image' );

			foreach ( $image_fields as $field ) {
				if ( ! empty( $block[ $field ] ) ) {
					if ( is_array( $block[ $field ] ) ) {
						foreach ( $block[ $field ] as $img ) {
							if ( is_string( $img ) ) {
								$images[] = str_replace( 'assets::', '', $img );
							}
						}
					} else {
						$images[] = str_replace( 'assets::', '', $block[ $field ] );
					}
				}
			}

			if ( ! empty( $block['photos'] ) ) {
				$this->collect_images_from_blocks( $block['photos'], $images );
			}
			if ( ! empty( $block['issues'] ) ) {
				$this->collect_images_from_blocks( $block['issues'], $images );
			}
			if ( ! empty( $block['content_field'] ) ) {
				$this->collect_images_from_blocks( $block['content_field'], $images );
			}
		}
	}

	/**
	 * Import a single Statamic entry.
	 *
	 * @param array $entry Statamic entry data.
	 * @return int|null The new post ID or null on failure.
	 */
	private function import_entry( $entry ) {
		$blocks = array();

		if ( ! empty( $entry['hero_image'] ) || ! empty( $entry['subheading'] ) ) {
			$blocks[] = $this->convert_hero_block( $entry, 0 );
		}

		if ( ! empty( $entry['content'] ) && is_array( $entry['content'] ) ) {
			foreach ( $entry['content'] as $block ) {
				if ( ! isset( $block['type'] ) || ( isset( $block['enabled'] ) && ! $block['enabled'] ) ) {
					continue;
				}

				switch ( $block['type'] ) {
					case 'content':
						$blocks[] = $this->convert_content_block( $block, 0 );
						break;
					case 'quote':
						$blocks[] = $this->convert_quote_block( $block, 0 );
						break;
					case 'photos':
						$blocks[] = $this->convert_photos_block( $block, 0 );
						break;
					case 'photo_break':
						$blocks[] = $this->convert_photo_break_block( $block, 0 );
						break;
					case 'recent_issues':
						$blocks[] = $this->convert_recent_issues_block( $block, 0 );
						break;
					case 'featured_stories':
						$blocks[] = $this->convert_featured_stories_block( $block, 0 );
						break;
					case 'more_stories':
						$blocks[] = $this->convert_more_stories_block( $block, 0 );
						break;
					default:
						WP_CLI::warning( "Unknown block type '{$block['type']}' in {$entry['title']}" );
						break;
				}
			}
		}

		$post_content = $this->blocks_to_gutenberg( $blocks );

		$post_id = wp_insert_post(
			array(
				'post_title'   => $entry['title'],
				'post_name'    => $entry['slug'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_date'    => $entry['updated_at'] ?? current_time( 'mysql' ),
				'post_content' => wp_slash( $post_content),
			)
		);

		if ( is_wp_error( $post_id ) ) {
			WP_CLI::warning( "Failed to import: {$entry['title']}" );
			return;
		}

		update_post_meta( $post_id, '_statamic_id', $entry['id']);

		$this->import_page_meta( $entry, $post_id );

		WP_CLI::line( "✓ Imported: {$entry['title']} (ID: {$post_id})" );
		return $post_id;
	}

	/**
	 * Assign parent relationships after all posts are created.
	 *
	 * @param array $blocks Gutenberg blocks.
	 * @return array Updated entries with parent IDs assigned.
	 */
	private function blocks_to_gutenberg( $blocks) {
		$gutenberg_blocks = array();

		foreach ( $blocks as $block_data ) {
			$block_name = 'acf/' . $block_data['acf_fc_layout'];
			$block_type = $block_data['acf_fc_layout'];
			unset( $block_data['acf_fc_layout'] );

			$data = $this->prepare_block_data( $block_data, $block_type );

			$json_data = wp_json_encode(
				array(
					'name' => $block_name,
					'data' => $data,
					'mode' => 'edit',
				)
			);

			$gutenberg_blocks[] = "<!-- wp:{$block_name} {$json_data} /-->";
		}

		return implode( "\n\n", $gutenberg_blocks );
	}

	/**
	 * Prepare the block data
	 *
	 * @param array  $block_data Block data.
	 * @param string $block_type Block type.
	 * @return array Prepared block data.
	 */
	private function prepare_block_data( $block_data, $block_type) {
		$prepared   = array();
		$field_keys = $this->field_keys[ $block_type ] ?? array();

		foreach ( $block_data as $key => $value ) {
			// Handle flexible content (block_content).
			if ( 'block_content' === $key && is_array( $value ) ) {
				// ACF blocks expect an array of layout names, not a count string.
				$layouts = array();
				foreach ( $value as $index => $layout_data ) {
					$layout_name = $layout_data['acf_fc_layout'] ?? 'text';
					$layouts[] = $layout_name;

					// Row subfields as block_content_{i}_{subkey}.
					foreach ( $layout_data as $layout_key => $layout_value ) {
						if ( 'acf_fc_layout' === $layout_key ) {
							continue;
						}

						$prepared[ "block_content_{$index}_{$layout_key}" ] = $layout_value;

						// Add subfield key if we have it mapped as block_content_{layout}_{subkey}
						$layout_field_key = "block_content_{$layout_name}_{$layout_key}";
						if (isset( $field_keys[$layout_field_key] ) ) {
							$prepared["_block_content_{$index}_{$layout_key}"] = $field_keys[$layout_field_key];
						}
					}
				}

				// The flexible field value itself is the layouts array
				$prepared['block_content'] = $layouts;

				// Field key for the flexible field
				$prepared['_block_content'] = $field_keys['block_content'] ?? '';

				// Optional but matches what the editor writes when you add rows
				$prepared['_block_content_layout_meta'] = [
					'disabled' => [],
					'renamed'  => [],
				];

				continue;
			}

			// Add the value
			$prepared[$key] = $value;

			// Add the field key reference
			if (isset( $field_keys[$key] ) ) {
				$prepared["_{$key}"] = $field_keys[$key];
			}

			// Handle repeaters (but NOT flexible content which we handled above)
			if (is_array( $value) && $this->is_repeater_field( $key ) ) {

				// Ensure the _<repeater> meta key is present
				if (isset( $field_keys[$key] ) ) {
					$prepared["_" . $key] = $field_keys[$key];
				}

				foreach ( $value as $index => $row_data) {
					if ( ! is_array( $row_data ) ) continue;

					foreach ( $row_data as $sub_key => $sub_value) {
						$prepared["{$key}_{$index}_{$sub_key}"] = $sub_value;

						// Add sub-field key if we have it
						$sub_field_key = "{$key}_{$sub_key}";
						if (isset( $field_keys[$sub_field_key] ) ) {
							$prepared["_{$key}_{$index}_{$sub_key}"] = $field_keys[$sub_field_key];
						}
					}
				}

				// Set the repeater count **after** its row fields, and as a string
				$prepared[$key] = (string) count( $value);

				continue; // done with this repeater
			}
		}

		return $prepared;
	}

	private function is_repeater_field( $key) {
		$repeater_fields = ['side_fixed_images', 'photos', 'recent_issues'];
		return in_array( $key, $repeater_fields);
	}

	private function import_page_meta( $entry, $post_id) {
		$keys = $this->field_keys['page_meta'];

		if (isset( $entry['story_type'] ) ) {
			update_post_meta( $post_id, 'story_type', $entry['story_type']);
			update_post_meta( $post_id, '_story_type', $keys['story_type']);
		}

		if (isset( $entry['story_author'] ) ) {
			update_post_meta( $post_id, 'author', $entry['story_author']);
			update_post_meta( $post_id, '_author', $keys['author']);
		}

		if (isset( $entry['edition_homepage'] ) ) {
			update_post_meta( $post_id, 'edition_homepage', $entry['edition_homepage']);
			update_post_meta( $post_id, '_edition_homepage', $keys['edition_homepage']);
		}

		if (isset( $entry['hide_link_back_to_edition'] ) ) {
			update_post_meta( $post_id, 'hide_link_back_to_edition', $entry['hide_link_back_to_edition']);
			update_post_meta( $post_id, '_hide_link_back_to_edition', $keys['hide_link_back_to_edition']);
		}
	}

	private function convert_hero_block( $entry, $post_id) {
		$hero_image_id = null;
		if ( ! empty( $entry['hero_image'] ) ) {
			$hero_image_id = $this->import_image( $entry['hero_image'], $post_id);
		}

		return [
			'acf_fc_layout' => 'hero',
			'hero_image' => $hero_image_id,
			'photo_credit' => $entry['photo_credit'] ?? '',
			'hide_post_title' => isset( $entry['hide_text_content'])
				? $this->acf_bool_str( $entry['hide_text_content'])
				: '0',
			'preheading' => $entry['preheading'] ?? '',
			'subheading' => $entry['subheading'] ?? '',
		];
	}

	private function convert_content_block( $block, $post_id) {
		$acf_block = [
			'acf_fc_layout' => 'basic-content',
			'background_style' => $block['background'] ?? 'color',
			'background_color' => $block['background_color'] ?? 'white',
			'content_side' => $block['content_side'] ?? 'left',
			'graphic_side_image' => $block['graphic_side_image'] ?? false,
		];

		if ( ! empty( $block['background_image'] ) ) {
			$acf_block['background_image'] = $this->import_image( $block['background_image'], $post_id);
			$acf_block['background_image_opacity'] = $block['background_image_opacity'] ?? 15;
		}

		if ( ! empty( $block['pattern'] ) ) {
			$acf_block['pattern'] = $block['pattern'];
		}

		if ( ! empty( $block['side_fixed_image']) && is_array( $block['side_fixed_image'] ) ) {
			$acf_block['side_fixed_images'] = array()
			foreach ( $block['side_fixed_image'] as $image_filename) {
				$image_id = $this->import_image( $image_filename, $post_id);
				if ( $image_id) {
					$acf_block['side_fixed_images'][] = ['image' => $image_id];
				}
			}
		}

		if ( ! empty( $block['content_field']) && is_array( $block['content_field'] ) ) {
			$acf_block['block_content'] = $this->convert_bard_to_flexible( $block['content_field'], $post_id);
		}

		return $acf_block;
	}

	private function convert_bard_to_flexible( $bard_content, $post_id) {
		$flexible_content = array()
		$text_buffer = array()

		foreach ( $bard_content as $item) {
			if (isset( $item['type']) && $item['type'] === 'set' ) {
				if ( ! empty( $text_buffer ) ) {
					$flexible_content[] = [
						'acf_fc_layout' => 'text',
						'content' => $this->convert_prosemirror_to_html( $text_buffer)
					];
					$text_buffer = array()
				}

				$set_type = $item['attrs']['values']['type'] ?? null;

				switch ( $set_type) {
					case 'graphics':
						$flexible_content[] = [
							'acf_fc_layout' => 'graphics',
							'graphic_type'  => $item['attrs']['values']['graphic'] ?? 'greensquiggle',
						];
						break;

					case 'note':
						$flexible_content[] = [
							'acf_fc_layout' => 'note',
							'title' => $item['attrs']['values']['title'] ?? '',
							'note_copy' => $this->convert_prosemirror_to_html( $item['attrs']['values']['note_copy'] ?? [])
						];
						break;

					case 'image':
						if ( ! empty( $item['attrs']['values']['image'] ) ) {
							$image_id = $this->import_image(
								$item['attrs']['values']['image'][0] ?? $item['attrs']['values']['image'],
								$post_id
							);
							$flexible_content[] = [
								'acf_fc_layout' => 'image',
								'image' => $image_id
							];
						}
						break;
				}
			} else {
				$text_buffer[] = $item;
			}
		}

		if ( ! empty( $text_buffer ) ) {
			$flexible_content[] = [
				'acf_fc_layout' => 'text',
				'content' => $this->convert_prosemirror_to_html( $text_buffer)
			];
		}

		return $flexible_content;
	}

	private function convert_prosemirror_to_html( $prosemirror_data) {
		if ( ! is_array( $prosemirror_data ) ) {
			return $prosemirror_data;
		}

		$html = '';

		foreach ( $prosemirror_data as $node) {
			if ( ! isset( $node['type'] ) ) continue;

			switch ( $node['type']) {
				case 'paragraph':
					$html .= '<p>' . $this->extract_text_from_node( $node) . '</p>';
					break;
				case 'heading':
					$level = $node['attrs']['level'] ?? 2;
					$html .= "<h{$level}>" . $this->extract_text_from_node( $node) . "</h{$level}>";
					break;
				case 'bulletList':
					$html .= '<ul>';
					if (isset( $node['content'] ) ) {
						foreach ( $node['content'] as $listItem) {
							$html .= '<li>' . $this->extract_text_from_node( $listItem) . '</li>';
						}
					}
					$html .= '</ul>';
					break;
				case 'orderedList':
					$html .= '<ol>';
					if (isset( $node['content'] ) ) {
						foreach ( $node['content'] as $listItem) {
							$html .= '<li>' . $this->extract_text_from_node( $listItem) . '</li>';
						}
					}
					$html .= '</ol>';
					break;
				case 'blockquote':
					$html .= '<blockquote>' . $this->extract_text_from_node( $node) . '</blockquote>';
					break;
				case 'horizontalRule':
					$html .= '<hr>';
					break;
				case 'table':
					$html .= $this->convert_table( $node);
					break;
			}
		}

		return $html;
	}

	private function extract_text_from_node( $node) {
		$text = '';

		if (isset( $node['content'] ) ) {
			foreach ( $node['content'] as $content) {
				if ( $content['type'] === 'text' ) {
					$text_content = $content['text'] ?? '';

					if (isset( $content['marks'] ) ) {
						foreach ( $content['marks'] as $mark) {
							switch ( $mark['type']) {
								case 'bold':
									$text_content = "<strong>{$text_content}</strong>";
									break;
								case 'italic':
									$text_content = "<em>{$text_content}</em>";
									break;
								case 'link':
									$href = $mark['attrs']['href'] ?? '#';
									$text_content = "<a href=\"{$href}\">{$text_content}</a>";
									break;
							}
						}
					}

					$text .= $text_content;
				} elseif ( $content['type'] === 'hardBreak' ) {
					$text .= '<br>';
				} else {
					$text .= $this->extract_text_from_node( $content);
				}
			}
		}

		return $text;
	}

	private function convert_table( $node) {
		$html = '<table>';

		if (isset( $node['content'] ) ) {
			foreach ( $node['content'] as $row) {
				$html .= '<tr>';
				if (isset( $row['content'] ) ) {
					foreach ( $row['content'] as $cell) {
						$tag = $cell['type'] === 'tableHeader' ? 'th' : 'td';
						$html .= "<{$tag}>" . $this->extract_text_from_node( $cell) . "</{$tag}>";
					}
				}
				$html .= '</tr>';
			}
		}

		$html .= '</table>';
		return $html;
	}

	private function convert_quote_block( $block, $post_id) {
		$acf_block = [
			'acf_fc_layout' => 'quote',
			'quote_placement' => $block['quote_side'] ?? 'center',
			'attribution' => $block['attribution'] ?? '',
			'sub_attribution' => $block['sub_attribution'] ?? '',
			'background' => $block['background'] ?? 'color',
			'background_color' => $block['background_color'] ?? 'green',
		];

		if ( ! empty( $block['quote_text']) && is_array( $block['quote_text'] ) ) {
			$acf_block['quote'] = $this->convert_prosemirror_to_html( $block['quote_text']);
		} elseif ( ! empty( $block['quote_text'] ) ) {
			$acf_block['quote'] = $block['quote_text'];
		}

		if ( ! empty( $block['background_image'] ) ) {
			$acf_block['background_image'] = $this->import_image( $block['background_image'], $post_id);
			$acf_block['quote_vertical_placement'] = $block['quote_placement'] ?? 'center';
		}

		return $acf_block;
	}

	private function convert_photos_block( $block, $post_id) {
		$acf_block = [
			'acf_fc_layout' => 'photos',
			'display_type'  => $block['display_type'] ?? 'polaroid',
			'fade_from_grayscale_to_color' => $this->acf_bool_str( $block['fade_color'] ?? false),
			'photos'        => [], // repeater rows
		];

		if ( ! empty( $block['photos']) && is_array( $block['photos'] ) ) {
			foreach ( $block['photos'] as $photo_item) {
				$row = array()

				if ( ! empty( $photo_item['photo'] ) ) {
					$photo = is_array( $photo_item['photo']) ? ( $photo_item['photo'][0] ?? null) : $photo_item['photo'];
					if ( $photo) {
						$row['photo'] = $this->import_image( $photo, $post_id);
					}
				}

				$row['cover_text']   = $photo_item['caption'] ?? '';
				if ( ! empty( $row['cover_text'] ) ) {
					$row['text_placement'] = 'center';
				}
				$row['photo_credit'] = $photo_item['photo_credit'] ?? '';

				$acf_block['photos'][] = $row;
			}
		}

		return $acf_block;
	}

	private function convert_photo_break_block( $block, $post_id) {
		$acf_block = [
			'acf_fc_layout' => 'photos',
			'display_type'  => 'full',
			'fade_from_grayscale_to_color' => $this->acf_bool_str( $block['fade_color'] ?? false),
			'photos'        => [],
		];

		$row = array()

		if ( ! empty( $block['photo'] ) ) {
			$photo = is_array( $block['photo']) ? ( $block['photo'][0] ?? null) : $block['photo'];
			if ( $photo) {
				$row['photo'] = $this->import_image( $photo, $post_id);
			}
		}

		$row['cover_text']    = $block['cover_text'] ?? '';
		$row['text_placement'] = $block['text_placement'] ?? 'center';
		$row['photo_credit']  = $block['photo_credit'] ?? '';

		$acf_block['photos'][] = $row;

		return $acf_block;
	}


	private function convert_recent_issues_block( $block, $post_id) {
		$acf_block = [
			'acf_fc_layout' => 'recent-issues',
			'recent_issues' => []
		];

		if ( ! empty( $block['issues']) && is_array( $block['issues'] ) ) {
			foreach ( $block['issues'] as $issue) {
				$repeater_row = array()

				$link_url = $issue['link_url'] ?? '';
				if (strpos( $link_url, 'entry::' ) === 0) {
					$link_url = $this->convert_statamic_entry_to_wp_url( $link_url);
				}

				if ( ! empty( $link_url ) ) {
					$repeater_row['link'] = [
						'url' => $link_url,
						'title' => $issue['link_name'] ?? '',
						'target' => ''
					];
				}

				if ( ! empty( $issue['cover_image'] ) ) {
					$cover_image = is_array( $issue['cover_image']) ? ( $issue['cover_image'][0] ?? null) : $issue['cover_image'];
					if ( $cover_image) {
						$repeater_row['cover_image'] = $this->import_image( $cover_image, $post_id);
					}
				}

				$acf_block['recent_issues'][] = $repeater_row;
			}
		}

		return $acf_block;
	}

	private function convert_featured_stories_block( $block, $post_id) {
		$acf_block = [
			'acf_fc_layout' => 'feature-stories',
			'stories' => []
		];

		if ( ! empty( $block['stories']) && is_array( $block['stories'] ) ) {
			foreach ( $block['stories'] as $story_data) {
				$statamic_entry_id = is_array( $story_data) ? ( $story_data['id'] ?? $story_data['story'] ?? $story_data) : $story_data;
				$wp_post_id = $this->get_wp_post_id_from_statamic_id( $statamic_entry_id);
				if ( $wp_post_id) {
					$acf_block['stories'][] = $wp_post_id;
				}
			}
		}

		return $acf_block;
	}

	private function convert_more_stories_block( $block, $post_id) {
		$acf_block = [
			'acf_fc_layout' => 'more-stories',
			'stories' => []
		];

		if ( ! empty( $block['more_stories']) && is_array( $block['more_stories'] ) ) {
			foreach ( $block['more_stories'] as $story_data) {
				$statamic_entry_id = is_array( $story_data) ? ( $story_data['id'] ?? $story_data) : $story_data;
				$wp_post_id = $this->get_wp_post_id_from_statamic_id( $statamic_entry_id);
				if ( $wp_post_id) {
					$acf_block['stories'][] = $wp_post_id;
				}
			}
		}

		return $acf_block;
	}

	private function convert_statamic_entry_to_wp_url( $entry_reference) {
		$entry_id = str_replace( 'entry::', '', $entry_reference);
		$args = [
			'post_type' => 'any',
			'meta_key' => '_statamic_id',
			'meta_value' => $entry_id,
			'posts_per_page' => 1,
			'fields' => 'ids'
		];
		$posts = get_posts( $args);
		if ( ! empty( $posts ) ) {
			return get_permalink( $posts[0]);
		}
		WP_CLI::warning( "Could not find WordPress post for Statamic entry: {$entry_id}" );
		return home_url();
	}

	private function get_wp_post_id_from_statamic_id( $statamic_id) {
		if (empty( $statamic_id ) ) {
			return null;
		}
		$statamic_id = str_replace( 'entry::', '', $statamic_id);
		$args = [
			'post_type' => 'any',
			'meta_key' => '_statamic_id',
			'meta_value' => $statamic_id,
			'posts_per_page' => 1,
			'fields' => 'ids'
		];
		$posts = get_posts( $args);
		if ( ! empty( $posts ) ) {
			return $posts[0];
		}
		WP_CLI::warning( "Could not find WordPress post for Statamic entry: {$statamic_id}" );
		return null;
	}

	private function find_attachment_id_by_filename( $filename) {
		global $wpdb;
		if (empty( $filename ) ) return null;

		$filename = str_replace( 'assets::', '', (string) $filename);
		if (is_array( $filename ) ) {
			$filename = $filename[0] ?? '';
		}
		if ( $filename === '' ) return null;

		$pi = pathinfo( $filename);
		$base = $pi['filename'] ?? $filename;
		$ext = isset( $pi['extension']) ? strtolower( $pi['extension']) : '';
		$exts = $ext ? array_unique([$ext, $ext === 'jpg' ? 'jpeg' : ( $ext === 'jpeg' ? 'jpg' : $ext)]) : ['jpg','jpeg','png','gif','webp'];

		$base_paren_clean = preg_replace( '/-\((\d+)\)/', '-$1', $base);
		$base_no_trailer = preg_replace( '/-\(?\d+\)?$/', '', $base_paren_clean);
		$sanitized = sanitize_file_name( $base_paren_clean . ( $ext ? ".{$ext}" : ''  ) );
		$san_pi = pathinfo( $sanitized);
		$san_base = $san_pi['filename'] ?? $base_paren_clean;

		$stems = array_values(array_unique(array_filter([
			$base,
			$base_paren_clean,
			$base_no_trailer,
			$san_base,
			$san_base . '-scaled',
			$base_paren_clean . '-scaled',
			$base_no_trailer . '-scaled',
			$base_paren_clean . '-1',
			$base_paren_clean . '-1-scaled',
		] ) ));

		foreach ( $stems as $stem) {
			foreach ( $exts as $tryExt) {
				$like = '%' . $wpdb->esc_like( $stem . '.' . $tryExt) . '%';
				$attachment_id = $wpdb->get_var( $wpdb->prepare(
					"SELECT pm.post_id FROM {$wpdb->postmeta} pm
					WHERE pm.meta_key = '_wp_attached_file' AND pm.meta_value LIKE %s LIMIT 1",
					$like
				 ) );
				if ( $attachment_id) return (int) $attachment_id;
			}

			$like = '%' . $wpdb->esc_like( $stem) . '%';
			$attachment_id = $wpdb->get_var( $wpdb->prepare(
				"SELECT pm.post_id FROM {$wpdb->postmeta} pm
				WHERE pm.meta_key = '_wp_attached_file' AND pm.meta_value LIKE %s LIMIT 1",
				$like
			 ) );
			if ( $attachment_id) return (int) $attachment_id;

			$attachment_id = $wpdb->get_var( $wpdb->prepare(
				"SELECT pm.post_id FROM {$wpdb->postmeta} pm
				WHERE pm.meta_key = '_wp_attachment_metadata' AND pm.meta_value LIKE %s LIMIT 1",
				$like
			 ) );
			if ( $attachment_id) return (int) $attachment_id;
		}

		return null;
	}

	private function acf_bool( $v) { return $v ? 1 : 0; }

	private function acf_bool_str( $v) { return $v ? '1' : '0'; }

	private function import_image( $filename, $post_id) {
		if (empty( $filename ) ) return null;

		if (isset( $this->image_cache[$filename] ) ) {
			return $this->image_cache[$filename];
		}

		if (is_array( $filename ) ) {
			$filename = $filename[0] ?? null;
		}
		if (empty( $filename ) ) return null;

		$attachment_id = $this->find_attachment_id_by_filename( $filename);

		if ( $attachment_id) {
			$this->image_cache[$filename] = $attachment_id;
			return $attachment_id;
		}

		WP_CLI::warning( "Image not found in Media Library: {$filename}" );
		return null;
	}

	private function assign_parent_relationships( $entries) {
		foreach ( $entries as $entry) {
			$parent_id_value = $entry['parent_id'] ?? null;

			// Skip if there's no parent or it's the pseudo "home" root.
			if (empty( $parent_id_value) || $parent_id_value === 'home' ) {
				continue;
			}

			$child_id  = $this->imported_map[$entry['id']] ?? null;
			$parent_id = $this->imported_map[$parent_id_value] ?? null;

			if ( $child_id && $parent_id) {
				wp_update_post([
					'ID'          => $child_id,
					'post_parent' => $parent_id,
				]);
				WP_CLI::line( "↳ Set parent for {$entry['title']} (#{$child_id}) → Parent #{$parent_id}" );
			} elseif ( $child_id && empty( $parent_id ) ) {
				WP_CLI::warning( "Parent not found for {$entry['title']} ({$entry['id']})" );
			}
		}
	}
}

WP_CLI::add_command( 'statamic import', ['Statamic_Importer_Command', 'import']);
WP_CLI::add_command( 'statamic check-images', ['Statamic_Importer_Command', 'check_images']);
