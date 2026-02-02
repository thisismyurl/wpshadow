<?php
/**
 * CPT Meta Box Support Diagnostic
 *
 * Checks if custom post types support meta boxes correctly. Tests add_meta_box
 * functionality and validates meta box registration for CPTs.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Meta Box Support Diagnostic Class
 *
 * Checks for meta box support issues with custom post types.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CPT_Meta_Box_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-meta-box-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Meta Box Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates meta box support and registration for custom post types';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cpt';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_meta_boxes;

		$issues = array();

		// Get all registered post types.
		$post_types = get_post_types( array(), 'objects' );

		// Filter to only custom post types.
		$built_in = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation' );
		$custom_post_types = array_filter(
			$post_types,
			function ( $pt ) use ( $built_in ) {
				return ! in_array( $pt->name, $built_in, true );
			}
		);

		if ( empty( $custom_post_types ) ) {
			return null;
		}

		foreach ( $custom_post_types as $cpt ) {
			// Skip if not showing UI.
			if ( ! $cpt->show_ui ) {
				continue;
			}

			// Check if CPT has meta boxes registered.
			$has_meta_boxes = isset( $wp_meta_boxes[ $cpt->name ] ) && ! empty( $wp_meta_boxes[ $cpt->name ] );

			if ( $has_meta_boxes ) {
				// Count meta boxes by context.
				$meta_box_contexts = array(
					'normal'   => 0,
					'side'     => 0,
					'advanced' => 0,
				);

				foreach ( $wp_meta_boxes[ $cpt->name ] as $context => $priority_boxes ) {
					if ( isset( $meta_box_contexts[ $context ] ) ) {
						foreach ( $priority_boxes as $priority => $boxes ) {
							$meta_box_contexts[ $context ] += count( $boxes );
						}
					}
				}

				// Check for excessive meta boxes in one context.
				if ( $meta_box_contexts['normal'] > 10 ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: number of meta boxes */
						__( 'CPT "%1$s" has %2$d meta boxes in normal context (may clutter edit screen)', 'wpshadow' ),
						esc_html( $cpt->name ),
						$meta_box_contexts['normal']
					);
				}

				if ( $meta_box_contexts['side'] > 8 ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: number of meta boxes */
						__( 'CPT "%1$s" has %2$d meta boxes in side context (may overflow sidebar)', 'wpshadow' ),
						esc_html( $cpt->name ),
						$meta_box_contexts['side']
					);
				}

				// Check if CPT supports custom-fields (needed for some meta box types).
				$supports_custom_fields = post_type_supports( $cpt->name, 'custom-fields' );
				if ( ! $supports_custom_fields && ( $meta_box_contexts['normal'] > 0 || $meta_box_contexts['side'] > 0 ) ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" has meta boxes but doesn\'t support custom-fields (may cause display issues)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}

				// Check for Gutenberg compatibility.
				if ( post_type_supports( $cpt->name, 'editor' ) && $cpt->show_in_rest ) {
					// Using block editor - check if meta boxes are compatible.
					foreach ( $wp_meta_boxes[ $cpt->name ] as $context => $priority_boxes ) {
						foreach ( $priority_boxes as $priority => $boxes ) {
							foreach ( $boxes as $box_id => $box ) {
								// Check if meta box callback is a string (function name).
								if ( is_string( $box['callback'] ) ) {
									// Check if function exists.
									if ( ! function_exists( $box['callback'] ) ) {
										$issues[] = sprintf(
											/* translators: 1: meta box ID, 2: post type slug, 3: callback function */
											__( 'Meta box "%1$s" for CPT "%2$s" has invalid callback "%3$s" (function doesn\'t exist)', 'wpshadow' ),
											esc_html( $box_id ),
											esc_html( $cpt->name ),
											esc_html( $box['callback'] )
										);
									}
								}
							}
						}
					}
				}
			} else {
				// Check if CPT should have meta boxes but doesn't.
				if ( post_type_supports( $cpt->name, 'custom-fields' ) ) {
					// Supports custom fields but no meta boxes registered - might be intentional.
					// Only warn if there's actual post meta stored.
					global $wpdb;
					$meta_count = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT COUNT(DISTINCT pm.meta_key)
							FROM {$wpdb->postmeta} pm
							INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
							WHERE p.post_type = %s
							AND pm.meta_key NOT LIKE '\\_%%'",
							$cpt->name
						)
					);

					if ( $meta_count > 5 ) {
						$issues[] = sprintf(
							/* translators: 1: post type slug, 2: number of meta keys */
							__( 'CPT "%1$s" has %2$d custom field types but no meta boxes registered (users can\'t edit)', 'wpshadow' ),
							esc_html( $cpt->name ),
							$meta_count
						);
					}
				}
			}

			// Check if standard WordPress meta boxes are removed for CPT.
			if ( $cpt->supports ) {
				$expected_boxes = array(
					'submitdiv'  => post_type_supports( $cpt->name, 'title' ) || post_type_supports( $cpt->name, 'editor' ),
					'categorydiv' => false, // Depends on taxonomies.
					'tagsdiv'    => false, // Depends on taxonomies.
					'postexcerpt' => post_type_supports( $cpt->name, 'excerpt' ),
					'trackbacksdiv' => post_type_supports( $cpt->name, 'trackbacks' ),
					'postcustom' => post_type_supports( $cpt->name, 'custom-fields' ),
					'commentstatusdiv' => post_type_supports( $cpt->name, 'comments' ),
					'slugdiv'    => true, // Usually always present.
					'authordiv'  => post_type_supports( $cpt->name, 'author' ),
				);

				// Check for taxonomies.
				$taxonomies = get_object_taxonomies( $cpt->name );
				if ( ! empty( $taxonomies ) ) {
					$expected_boxes['categorydiv'] = true;
					$expected_boxes['tagsdiv'] = true;
				}

				// Count how many expected boxes are present.
				$missing_boxes = 0;
				if ( $has_meta_boxes ) {
					foreach ( $expected_boxes as $box_id => $expected ) {
						if ( $expected ) {
							$found = false;
							foreach ( $wp_meta_boxes[ $cpt->name ] as $context => $priority_boxes ) {
								foreach ( $priority_boxes as $priority => $boxes ) {
									if ( isset( $boxes[ $box_id ] ) ) {
										$found = true;
										break 3;
									}
								}
							}
							if ( ! $found ) {
								++$missing_boxes;
							}
						}
					}
				}

				if ( $missing_boxes > 3 ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: number of missing boxes */
						__( 'CPT "%1$s" missing %2$d expected WordPress meta boxes (manually removed?)', 'wpshadow' ),
						esc_html( $cpt->name ),
						$missing_boxes
					);
				}
			}

			// Check for duplicate meta box IDs.
			if ( $has_meta_boxes ) {
				$all_box_ids = array();
				foreach ( $wp_meta_boxes[ $cpt->name ] as $context => $priority_boxes ) {
					foreach ( $priority_boxes as $priority => $boxes ) {
						foreach ( array_keys( $boxes ) as $box_id ) {
							if ( isset( $all_box_ids[ $box_id ] ) ) {
								$issues[] = sprintf(
									/* translators: 1: meta box ID, 2: post type slug */
									__( 'Duplicate meta box ID "%1$s" for CPT "%2$s" (will cause display issues)', 'wpshadow' ),
									esc_html( $box_id ),
									esc_html( $cpt->name )
								);
							}
							$all_box_ids[ $box_id ] = true;
						}
					}
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cpt-meta-box-support',
			);
		}

		return null;
	}
}
