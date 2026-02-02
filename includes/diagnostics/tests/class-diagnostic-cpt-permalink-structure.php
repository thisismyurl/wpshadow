<?php
/**
 * CPT Permalink Structure Diagnostic
 *
 * Checks if CPT permalink structure is properly configured. Validates rewrite rules,
 * slug conflicts, and permalink accessibility for custom post types.
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
 * CPT Permalink Structure Diagnostic Class
 *
 * Checks for permalink configuration issues with custom post types.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CPT_Permalink_Structure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-permalink-structure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Permalink Structure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates CPT permalink structure and rewrite rules are properly configured';

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

		// Check if permalinks are enabled (not Plain).
		$permalink_structure = get_option( 'permalink_structure', '' );
		if ( empty( $permalink_structure ) ) {
			$issues[] = __( 'Permalinks set to Plain (CPT URLs won\'t work)', 'wpshadow' );
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

		// Get all page slugs for conflict detection.
		$page_slugs = $wpdb->get_col(
			"SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status IN ('publish', 'private')"
		);

		foreach ( $custom_post_types as $cpt ) {
			// Skip if not public.
			if ( ! $cpt->public || ! $cpt->publicly_queryable ) {
				continue;
			}

			// Check if rewrite is disabled but CPT has posts.
			if ( false === $cpt->rewrite ) {
				$post_count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
						$cpt->name
					)
				);

				if ( $post_count > 0 ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: number of posts */
						__( 'CPT "%1$s" has %2$d published posts but rewrite disabled (URLs won\'t work)', 'wpshadow' ),
						esc_html( $cpt->name ),
						$post_count
					);
				}
			}

			// Check rewrite slug conflicts with pages.
			if ( $cpt->rewrite && ! empty( $cpt->rewrite['slug'] ) ) {
				if ( in_array( $cpt->rewrite['slug'], $page_slugs, true ) ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: rewrite slug */
						__( 'CPT "%1$s" rewrite slug "%2$s" conflicts with existing page', 'wpshadow' ),
						esc_html( $cpt->name ),
						esc_html( $cpt->rewrite['slug'] )
					);
				}

				// Check for conflicts with other CPTs.
				foreach ( $custom_post_types as $other_cpt ) {
					if ( $cpt->name === $other_cpt->name ) {
						continue;
					}
					if ( $other_cpt->rewrite && ! empty( $other_cpt->rewrite['slug'] ) && $cpt->rewrite['slug'] === $other_cpt->rewrite['slug'] ) {
						$issues[] = sprintf(
							/* translators: 1: post type slug, 2: conflicting post type */
							__( 'CPT "%1$s" rewrite slug conflicts with "%2$s"', 'wpshadow' ),
							esc_html( $cpt->name ),
							esc_html( $other_cpt->name )
						);
					}
				}
			}

			// Check if rewrite rules exist for this CPT.
			$rewrite_rules = get_option( 'rewrite_rules', array() );
			$has_rules = false;

			if ( is_array( $rewrite_rules ) ) {
				$slug = $cpt->rewrite && ! empty( $cpt->rewrite['slug'] ) ? $cpt->rewrite['slug'] : $cpt->name;
				foreach ( $rewrite_rules as $pattern => $replacement ) {
					if ( false !== strpos( $pattern, $slug ) || false !== strpos( $replacement, 'post_type=' . $cpt->name ) ) {
						$has_rules = true;
						break;
					}
				}
			}

			if ( ! $has_rules && $cpt->rewrite ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" missing rewrite rules (permalinks may need flushing)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}

			// Check if CPT uses %postname% in structure.
			if ( $cpt->rewrite && isset( $cpt->rewrite['with_front'] ) && $cpt->rewrite['with_front'] ) {
				// Verify site permalink structure has front portion.
				if ( ! empty( $permalink_structure ) && 0 !== strpos( $permalink_structure, '/%postname%' ) ) {
					// Has front portion - check if it makes sense.
					$front_portion = substr( $permalink_structure, 0, strpos( $permalink_structure, '%' ) );
					if ( ! empty( $front_portion ) && strlen( $front_portion ) > 1 ) {
						// CPT will inherit this front portion - might be confusing.
						$issues[] = sprintf(
							/* translators: 1: post type slug, 2: front portion */
							__( 'CPT "%1$s" will use site permalink front "%2$s" (may cause unexpected URLs)', 'wpshadow' ),
							esc_html( $cpt->name ),
							esc_html( trim( $front_portion, '/' ) )
						);
					}
				}
			}

			// Check for CPT posts with empty post_name (no permalink generated).
			$empty_permalink = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} 
					WHERE post_type = %s 
					AND post_status = 'publish' 
					AND (post_name = '' OR post_name IS NULL)",
					$cpt->name
				)
			);

			if ( $empty_permalink > 0 ) {
				$issues[] = sprintf(
					/* translators: 1: number of posts, 2: post type slug */
					__( '%1$d "%2$s" posts have no permalink/slug (inaccessible)', 'wpshadow' ),
					$empty_permalink,
					esc_html( $cpt->name )
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cpt-permalink-structure',
			);
		}

		return null;
	}
}
