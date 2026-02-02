<?php
/**
 * CPT Gutenberg Support Diagnostic
 *
 * Verifies custom post types support Gutenberg editor correctly. Tests show_in_rest
 * configuration and editor compatibility for block editor functionality.
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
 * CPT Gutenberg Support Diagnostic Class
 *
 * Checks for Gutenberg editor compatibility issues with custom post types.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CPT_Gutenberg_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-gutenberg-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Gutenberg Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies CPTs support Gutenberg editor correctly and are REST API accessible';

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
		$issues = array();

		// Check if Gutenberg is active (block editor).
		if ( ! function_exists( 'use_block_editor_for_post_type' ) ) {
			// Block editor not available - skip check.
			return null;
		}

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
			// Skip if not public.
			if ( ! $cpt->public || ! $cpt->show_ui ) {
				continue;
			}

			// Check if CPT supports editor.
			$supports_editor = post_type_supports( $cpt->name, 'editor' );

			if ( $supports_editor ) {
				// If supports editor, must be in REST API for Gutenberg.
				if ( ! $cpt->show_in_rest ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" supports editor but show_in_rest is false (Gutenberg will not work)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}

				// Check if block editor is enabled for this CPT.
				$can_use_block_editor = use_block_editor_for_post_type( $cpt->name );
				if ( ! $can_use_block_editor ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" supports editor but block editor disabled (use_block_editor_for_post_type)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}

				// Check if custom fields are supported (required for meta boxes in Gutenberg).
				$supports_custom_fields = post_type_supports( $cpt->name, 'custom-fields' );
				if ( ! $supports_custom_fields ) {
					// This is OK, but worth noting if there are meta boxes.
					global $wp_meta_boxes;
					if ( isset( $wp_meta_boxes[ $cpt->name ] ) && ! empty( $wp_meta_boxes[ $cpt->name ] ) ) {
						$meta_box_count = 0;
						foreach ( $wp_meta_boxes[ $cpt->name ] as $context => $priority_boxes ) {
							foreach ( $priority_boxes as $priority => $boxes ) {
								$meta_box_count += count( $boxes );
							}
						}
						if ( $meta_box_count > 0 ) {
							$issues[] = sprintf(
								/* translators: 1: post type slug, 2: number of meta boxes */
								__( 'CPT "%1$s" has %2$d meta boxes but doesn\'t support custom-fields (may not display in Gutenberg)', 'wpshadow' ),
								esc_html( $cpt->name ),
								$meta_box_count
							);
						}
					}
				}
			}

			// Check if CPT has revisions support (important for Gutenberg).
			if ( $supports_editor && ! post_type_supports( $cpt->name, 'revisions' ) ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" supports editor but not revisions (Gutenberg auto-save won\'t work properly)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}

			// Check if CPT has title support (usually needed).
			if ( $supports_editor && ! post_type_supports( $cpt->name, 'title' ) ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" supports editor but not title (may cause Gutenberg issues)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}

			// Check if REST controller exists and is valid.
			if ( $cpt->show_in_rest ) {
				$rest_controller_class = ! empty( $cpt->rest_controller_class ) ? $cpt->rest_controller_class : 'WP_REST_Posts_Controller';
				if ( ! class_exists( $rest_controller_class ) ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: controller class */
						__( 'CPT "%1$s" REST controller class "%2$s" does not exist (block editor will fail)', 'wpshadow' ),
						esc_html( $cpt->name ),
						esc_html( $rest_controller_class )
					);
				}
			}

			// Check for classic editor plugin conflict.
			if ( $supports_editor && $cpt->show_in_rest ) {
				// Check if Classic Editor plugin is forcing classic editor.
				if ( class_exists( 'Classic_Editor' ) ) {
					$classic_editor_option = get_option( 'classic-editor-replace', '' );
					if ( 'classic' === $classic_editor_option ) {
						$issues[] = sprintf(
							/* translators: %s: post type slug */
							__( 'CPT "%s" configured for Gutenberg but Classic Editor plugin forcing classic editor', 'wpshadow' ),
							esc_html( $cpt->name )
						);
					}
				}
			}

			// Check if CPT has excerpt support (useful in Gutenberg).
			if ( $supports_editor && ! post_type_supports( $cpt->name, 'excerpt' ) ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" doesn\'t support excerpt (Gutenberg excerpt panel won\'t be available)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}
		}

		// Check if Gutenberg is disabled site-wide.
		$gutenberg_disabled = apply_filters( 'use_block_editor_for_post', true, get_post_type_object( 'post' ) );
		if ( false === $gutenberg_disabled ) {
			$issues[] = __( 'Block editor disabled site-wide via use_block_editor_for_post filter', 'wpshadow' );
		}

		// Check if REST API is accessible.
		if ( ! get_option( 'permalink_structure' ) ) {
			$issues[] = __( 'Pretty permalinks disabled (required for Gutenberg REST API)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cpt-gutenberg-support',
			);
		}

		return null;
	}
}
