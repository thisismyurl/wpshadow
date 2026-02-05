<?php
/**
 * CPT Permalink Structure Treatment
 *
 * Checks if custom post type permalinks work correctly by validating
 * rewrite rules and URL structure. Detects broken rewrites and 404 errors.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Permalink Structure Class
 *
 * Verifies custom post type permalinks are properly configured with
 * working rewrite rules. Detects issues causing 404 errors or incorrect URLs.
 *
 * @since 1.6030.2148
 */
class Treatment_CPT_Permalink_Structure extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-permalink-structure';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Permalink Structure';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CPT permalinks work correctly';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check.
	 *
	 * Validates custom post type permalink configuration and rewrite rules.
	 * Detects broken rewrites, missing slugs, and slug conflicts.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if permalink issues found, null otherwise.
	 */
	public static function check() {
		global $wp_rewrite, $wp_post_types;

		$issues = array();
		$problematic_cpts = array();

		// Check if permalinks are enabled.
		if ( ! get_option( 'permalink_structure' ) ) {
			$issues[] = __( 'Permalinks are set to "Plain" which prevents pretty CPT URLs from working.', 'wpshadow' );
		}

		// Get all public custom post types.
		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		if ( empty( $post_types ) ) {
			// No custom post types, nothing to check.
			return null;
		}

		// Check each CPT for permalink issues.
		foreach ( $post_types as $post_type => $post_type_obj ) {
			$cpt_issues = array();

			// Check if rewrite is enabled.
			if ( ! $post_type_obj->rewrite ) {
				$cpt_issues[] = __( 'Rewrite disabled - permalinks will not work', 'wpshadow' );
			} else {
				// Check rewrite slug.
				$slug = is_array( $post_type_obj->rewrite ) && isset( $post_type_obj->rewrite['slug'] )
					? $post_type_obj->rewrite['slug']
					: $post_type;

				// Check for slug conflicts with pages/posts.
				$conflict = get_page_by_path( $slug, OBJECT, 'page' );

				if ( $conflict ) {
					$cpt_issues[] = sprintf(
						/* translators: 1: CPT slug, 2: conflicting page ID */
						__( 'Slug "%1$s" conflicts with page ID %2$d', 'wpshadow' ),
						$slug,
						$conflict->ID
					);
				}

				// Check if slug conflicts with other CPTs.
				foreach ( $post_types as $other_type => $other_obj ) {
					if ( $other_type === $post_type ) {
						continue;
					}

					$other_slug = is_array( $other_obj->rewrite ) && isset( $other_obj->rewrite['slug'] )
						? $other_obj->rewrite['slug']
						: $other_type;

					if ( $slug === $other_slug ) {
						$cpt_issues[] = sprintf(
							/* translators: %s: conflicting post type */
							__( 'Slug conflicts with post type "%s"', 'wpshadow' ),
							$other_type
						);
					}
				}
			}

			// Check if rewrite rules exist for this CPT.
			$rewrite_rules = get_option( 'rewrite_rules', array() );
			$has_rules = false;

			if ( $post_type_obj->rewrite && is_array( $rewrite_rules ) ) {
				$slug = is_array( $post_type_obj->rewrite ) && isset( $post_type_obj->rewrite['slug'] )
					? $post_type_obj->rewrite['slug']
					: $post_type;

				foreach ( $rewrite_rules as $pattern => $replacement ) {
					if ( strpos( $pattern, $slug ) !== false ) {
						$has_rules = true;
						break;
					}
				}

				if ( ! $has_rules ) {
					$cpt_issues[] = __( 'No rewrite rules found - flush permalinks needed', 'wpshadow' );
				}
			}

			// Check if CPT has posts but no rewrite.
			$post_count = wp_count_posts( $post_type );
			$total_posts = isset( $post_count->publish ) ? $post_count->publish : 0;

			if ( $total_posts > 0 && ! $post_type_obj->rewrite ) {
				$cpt_issues[] = sprintf(
					/* translators: %d: number of posts */
					_n(
						'Has %d published post but permalinks are disabled',
						'Has %d published posts but permalinks are disabled',
						$total_posts,
						'wpshadow'
					),
					number_format_i18n( $total_posts )
				);
			}

			if ( ! empty( $cpt_issues ) ) {
				$problematic_cpts[ $post_type ] = array(
					'label'  => $post_type_obj->label,
					'slug'   => isset( $slug ) ? $slug : $post_type,
					'issues' => $cpt_issues,
					'posts'  => $total_posts,
				);

				$issues[] = sprintf(
					/* translators: 1: post type label, 2: list of issues */
					__( '%1$s: %2$s', 'wpshadow' ),
					$post_type_obj->label,
					implode( ', ', $cpt_issues )
				);
			}
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: number of CPTs with issues */
				_n(
					'Found permalink issues in %d custom post type: ',
					'Found permalink issues in %d custom post types: ',
					count( $problematic_cpts ),
					'wpshadow'
				) . implode( ' ', $issues ),
				number_format_i18n( count( $problematic_cpts ) )
			),
			'severity'    => 'medium',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cpt-permalink-structure',
			'details'     => array(
				'problematic_cpts'   => $problematic_cpts,
				'permalink_structure' => get_option( 'permalink_structure' ),
			),
		);
	}
}
