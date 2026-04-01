<?php
/**
 * CPT Registration Validation Diagnostic
 *
 * Validates all custom post types are registered correctly. Checks for registration errors,
 * slug conflicts, and improper configurations that could break functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Registration Validation Diagnostic Class
 *
 * Checks for issues with custom post type registration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_CPT_Registration_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-registration-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Registration Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates all custom post types are registered correctly without errors or conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cpt';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get all registered post types.
		$post_types = get_post_types( array(), 'objects' );

		// Filter to only custom post types (exclude built-in).
		$built_in          = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation' );
		$custom_post_types = array_filter(
			$post_types,
			function ( $pt ) use ( $built_in ) {
				return ! in_array( $pt->name, $built_in, true );
			}
		);

		if ( empty( $custom_post_types ) ) {
			// No custom post types - not necessarily an issue.
			return null;
		}

		// Check for reserved post type slugs.
		$reserved_slugs = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'action', 'author', 'order', 'theme' );
		foreach ( $custom_post_types as $cpt ) {
			if ( in_array( $cpt->name, $reserved_slugs, true ) ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" uses reserved WordPress slug', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}

			// Check for slug length (max 20 characters).
			if ( strlen( $cpt->name ) > 20 ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" slug exceeds 20 characters (may cause database issues)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}

			// Check for uppercase characters in slug (bad practice).
			if ( strtolower( $cpt->name ) !== $cpt->name ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" contains uppercase characters (should be lowercase)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}

			// Check for missing labels (required for proper display).
			if ( empty( $cpt->labels->name ) || empty( $cpt->labels->singular_name ) ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" missing required labels', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}

			// Check if supports is empty (no editor, title, etc.).
			$supports = get_all_post_type_supports( $cpt->name );
			if ( empty( $supports ) ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" doesn\'t support any features (no editor, title, etc.)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}

			// Check for rewrite conflicts.
			if ( $cpt->rewrite && ! empty( $cpt->rewrite['slug'] ) ) {
				// Check if rewrite slug conflicts with existing post types.
				foreach ( $post_types as $other_pt ) {
					if ( $cpt->name === $other_pt->name ) {
						continue;
					}
					if ( $other_pt->rewrite && ! empty( $other_pt->rewrite['slug'] ) && $cpt->rewrite['slug'] === $other_pt->rewrite['slug'] ) {
						$issues[] = sprintf(
							/* translators: 1: post type slug, 2: conflicting slug */
							__( 'CPT "%1$s" rewrite slug conflicts with "%2$s"', 'wpshadow' ),
							esc_html( $cpt->name ),
							esc_html( $other_pt->name )
						);
					}
				}

				// Check if rewrite slug conflicts with page slugs.
				$page_slugs = get_posts(
					array(
						'post_type'   => 'page',
						'numberposts' => -1,
						'fields'      => 'post_name',
					)
				);

				if ( in_array( $cpt->rewrite['slug'], $page_slugs, true ) ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" rewrite slug conflicts with existing page', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}
			}

			// Check if public but not publicly_queryable (usually an error).
			if ( $cpt->public && ! $cpt->publicly_queryable ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" is public but not publicly queryable (may cause 404 errors)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}

			// Check if has_archive is true but rewrite is false (conflict).
			if ( $cpt->has_archive && false === $cpt->rewrite ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" has archives enabled but rewrite disabled (archive inaccessible)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}
		}

		// Check for duplicate registrations (registered multiple times).
		$init_hooks = $GLOBALS['wp_filter']['init'] ?? null;
		if ( $init_hooks ) {
			$register_post_type_calls = 0;
			foreach ( $init_hooks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					$function_name = '';
					if ( is_array( $callback['function'] ) && is_string( $callback['function'][1] ) ) {
						$function_name = $callback['function'][1];
					} elseif ( is_string( $callback['function'] ) ) {
						$function_name = $callback['function'];
					}

					if ( false !== stripos( $function_name, 'register' ) && false !== stripos( $function_name, 'post' ) ) {
						++$register_post_type_calls;
					}
				}
			}

			if ( $register_post_type_calls > count( $custom_post_types ) * 2 ) {
				$issues[] = sprintf(
					/* translators: %d: number of registration calls */
					__( '%d post type registration hooks found (possible duplicate registrations)', 'wpshadow' ),
					$register_post_type_calls
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-registration-validation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
