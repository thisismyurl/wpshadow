<?php
/**
 * Custom Post Type Permalinks Treatment
 *
 * Validates CPT permalink structures and tests rewrite slug configuration.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1402
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Post Type Permalinks Treatment Class
 *
 * Checks for properly configured permalink structures and rewrite rules
 * for custom post types to ensure clean, SEO-friendly URLs.
 *
 * @since 1.6032.1402
 */
class Treatment_Custom_Post_Type_Permalinks extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-post-type-permalinks';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Post Type Permalinks';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates CPT permalink structures and rewrite slug configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * Validates custom post type permalink configurations including:
	 * - Rewrite slug configuration
	 * - Hierarchical structure support
	 * - Permalink conflicts with existing pages/posts
	 * - Archive page URL structure
	 *
	 * @since  1.6032.1402
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_rewrite;

		// Get all registered custom post types (exclude built-in).
		$post_types = get_post_types( array( '_builtin' => false ), 'objects' );

		if ( empty( $post_types ) ) {
			// No custom post types registered, nothing to check.
			return null;
		}

		$issues                 = array();
		$problematic_post_types = array();

		foreach ( $post_types as $post_type ) {
			// Skip non-public post types.
			if ( ! $post_type->public ) {
				continue;
			}

			$post_type_issues = array();
			$post_type_name   = $post_type->name;

			// Check if rewrites are disabled.
			if ( false === $post_type->rewrite ) {
				$post_type_issues[] = __( 'Rewrites disabled', 'wpshadow' );
			} elseif ( is_array( $post_type->rewrite ) ) {
				$rewrite = $post_type->rewrite;

				// Check for missing or problematic rewrite slug.
				if ( empty( $rewrite['slug'] ) ) {
					$post_type_issues[] = __( 'Missing rewrite slug', 'wpshadow' );
				} else {
					$slug = $rewrite['slug'];

					// Check for slug conflicts with reserved WordPress slugs.
					$reserved_slugs = array( 'post', 'page', 'admin', 'wp-admin', 'wp-content', 'wp-includes', 'feed', 'rsd', 'trackback', 'comments', 'attachment' );
					if ( in_array( $slug, $reserved_slugs, true ) ) {
						$post_type_issues[] = sprintf(
							/* translators: %s: rewrite slug */
							__( 'Slug "%s" conflicts with reserved WordPress slug', 'wpshadow' ),
							$slug
						);
					}

					// Check for slug conflicts with existing pages.
					$page = get_page_by_path( $slug );
					if ( $page ) {
						$post_type_issues[] = sprintf(
							/* translators: %s: rewrite slug */
							__( 'Slug "%s" conflicts with existing page', 'wpshadow' ),
							$slug
						);
					}

					// Check for potentially problematic slug patterns.
					if ( preg_match( '/[^a-z0-9\-_\/]/', $slug ) ) {
						$post_type_issues[] = sprintf(
							/* translators: %s: rewrite slug */
							__( 'Slug "%s" contains invalid characters', 'wpshadow' ),
							$slug
						);
					}
				}

				// Check hierarchical configuration.
				if ( $post_type->hierarchical && ! isset( $rewrite['hierarchical'] ) ) {
					$post_type_issues[] = __( 'Hierarchical post type missing hierarchical rewrite setting', 'wpshadow' );
				}
			}

			// Check if has_archive is set for post types that should have archives.
			if ( false === $post_type->has_archive && true === $post_type->public && true !== $post_type->exclude_from_search ) {
				// Only flag if the post type is queryable and public.
				if ( $post_type->publicly_queryable ) {
					$post_type_issues[] = __( 'Archive disabled for public post type', 'wpshadow' );
				}
			}

			// If we found issues with this post type, add to the list.
			if ( ! empty( $post_type_issues ) ) {
				$problematic_post_types[ $post_type->label ] = $post_type_issues;
			}
		}

		// Check if pretty permalinks are enabled.
		if ( '' === get_option( 'permalink_structure' ) ) {
			$issues[] = __( 'Pretty permalinks are not enabled. Custom post type permalinks require pretty permalinks to be enabled.', 'wpshadow' );
		}

		// If we found problematic post types, format the issues.
		if ( ! empty( $problematic_post_types ) ) {
			foreach ( $problematic_post_types as $label => $type_issues ) {
				$issues[] = sprintf(
					/* translators: 1: post type label, 2: comma-separated list of issues */
					__( '%1$s: %2$s', 'wpshadow' ),
					$label,
					implode( ', ', $type_issues )
				);
			}
		}

		// Return finding if issues exist.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of issues */
					__( 'Custom post type permalink issues detected: %s', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'      => array(
					'post_types'             => array_keys( $post_types ),
					'problematic_post_types' => $problematic_post_types,
					'issues'                 => $issues,
					'permalink_structure'    => get_option( 'permalink_structure' ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/custom-post-type-permalinks',
			);
		}

		return null;
	}
}
