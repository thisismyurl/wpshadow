<?php
/**
 * CPT REST API Exposure Diagnostic
 *
 * Checks if custom post types are exposed to REST API when intended. Validates
 * show_in_rest settings and REST base configuration for CPTs.
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
 * CPT REST API Exposure Diagnostic Class
 *
 * Checks for REST API configuration issues with custom post types.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CPT_REST_API_Exposure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-rest-api-exposure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT REST API Exposure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates CPTs are properly exposed to REST API when intended';

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
		global $wpdb;

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
			// Check if CPT is public but not in REST (might be intentional).
			if ( $cpt->public && ! $cpt->show_in_rest ) {
				// Check if this might need REST API (has many posts or uses block editor).
				$post_count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
						$cpt->name
					)
				);

				// Check if CPT supports 'editor' (likely using Gutenberg).
				$supports_editor = post_type_supports( $cpt->name, 'editor' );

				if ( $supports_editor ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" supports block editor but show_in_rest is false (Gutenberg won\'t work)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				} elseif ( $post_count > 50 ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: number of posts */
						__( 'CPT "%1$s" has %2$d posts but not exposed to REST API (may limit functionality)', 'wpshadow' ),
						esc_html( $cpt->name ),
						$post_count
					);
				}
			}

			// If CPT is in REST, validate configuration.
			if ( $cpt->show_in_rest ) {
				// Check if rest_base is set and valid.
				if ( empty( $cpt->rest_base ) ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" exposed to REST but rest_base is empty (will use post type slug)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}

				// Check if rest_base conflicts with other CPTs or built-in endpoints.
				$rest_base = ! empty( $cpt->rest_base ) ? $cpt->rest_base : $cpt->name;
				$reserved_rest_bases = array( 'posts', 'pages', 'media', 'types', 'statuses', 'taxonomies', 'comments', 'users', 'settings', 'themes', 'plugins', 'search', 'blocks' );

				if ( in_array( $rest_base, $reserved_rest_bases, true ) ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: rest base */
						__( 'CPT "%1$s" REST base "%2$s" conflicts with built-in WordPress endpoint', 'wpshadow' ),
						esc_html( $cpt->name ),
						esc_html( $rest_base )
					);
				}

				// Check for rest_base conflicts with other CPTs.
				foreach ( $custom_post_types as $other_cpt ) {
					if ( $cpt->name === $other_cpt->name || ! $other_cpt->show_in_rest ) {
						continue;
					}

					$other_rest_base = ! empty( $other_cpt->rest_base ) ? $other_cpt->rest_base : $other_cpt->name;
					if ( $rest_base === $other_rest_base ) {
						$issues[] = sprintf(
							/* translators: 1: post type slug, 2: conflicting post type */
							__( 'CPT "%1$s" REST base conflicts with "%2$s"', 'wpshadow' ),
							esc_html( $cpt->name ),
							esc_html( $other_cpt->name )
						);
					}
				}

				// Check if rest_controller_class is set and exists.
				if ( ! empty( $cpt->rest_controller_class ) && ! class_exists( $cpt->rest_controller_class ) ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: controller class */
						__( 'CPT "%1$s" REST controller class "%2$s" does not exist', 'wpshadow' ),
						esc_html( $cpt->name ),
						esc_html( $cpt->rest_controller_class )
					);
				}

				// Verify REST API is accessible (basic check).
				$rest_url = rest_url( 'wp/v2/' . $rest_base );
				if ( empty( $rest_url ) ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" REST URL cannot be generated', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}

				// Check if REST namespace is using old v1 (should be v2).
				if ( ! empty( $cpt->rest_namespace ) && 'wp/v1' === $cpt->rest_namespace ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" using deprecated REST namespace "wp/v1" (should use "wp/v2")', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}
			}

			// Check if CPT taxonomies are also exposed to REST.
			if ( $cpt->show_in_rest ) {
				$cpt_taxonomies = get_object_taxonomies( $cpt->name, 'objects' );
				foreach ( $cpt_taxonomies as $taxonomy ) {
					if ( ! $taxonomy->show_in_rest ) {
						$issues[] = sprintf(
							/* translators: 1: taxonomy name, 2: post type slug */
							__( 'Taxonomy "%1$s" for CPT "%2$s" not exposed to REST (may cause issues in block editor)', 'wpshadow' ),
							esc_html( $taxonomy->name ),
							esc_html( $cpt->name )
						);
					}
				}
			}
		}

		// Check if REST API is disabled globally (affects all CPTs).
		if ( ! get_option( 'permalink_structure' ) ) {
			$issues[] = __( 'REST API requires pretty permalinks (currently using Plain)', 'wpshadow' );
		}

		// Check for REST API authentication issues.
		if ( ! function_exists( 'rest_cookie_check_errors' ) ) {
			$issues[] = __( 'REST API cookie authentication unavailable (may cause block editor issues)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cpt-rest-api-exposure',
			);
		}

		return null;
	}
}
