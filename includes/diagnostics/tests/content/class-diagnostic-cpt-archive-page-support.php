<?php
/**
 * CPT Archive Page Support Diagnostic
 *
 * Verifies custom post type archive pages display correctly by validating
 * has_archive settings and testing archive functionality.
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
 * CPT Archive Page Support Class
 *
 * Ensures custom post type archives are properly configured and accessible.
 * Detects disabled archives on CPTs with published posts.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CPT_Archive_Page_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-archive-page-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Archive Page Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies CPT archive pages display correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates custom post type archive functionality. Detects CPTs with
	 * published posts but disabled archives.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if archive issues found, null otherwise.
	 */
	public static function check() {
		global $wp_post_types;

		$issues = array();
		$problematic_cpts = array();

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

		// Check each CPT for archive issues.
		foreach ( $post_types as $post_type => $post_type_obj ) {
			$cpt_issues = array();

			// Get post count.
			$post_count = wp_count_posts( $post_type );
			$total_posts = isset( $post_count->publish ) ? $post_count->publish : 0;

			// Skip if no posts.
			if ( $total_posts === 0 ) {
				continue;
			}

			// Check if has_archive is disabled.
			if ( ! $post_type_obj->has_archive ) {
				$cpt_issues[] = sprintf(
					/* translators: %d: number of posts */
					_n(
						'Has %d published post but archive is disabled',
						'Has %d published posts but archive is disabled',
						$total_posts,
						'wpshadow'
					),
					number_format_i18n( $total_posts )
				);
			} else {
				// Archive is enabled - check if rewrite rules exist.
				$archive_slug = true === $post_type_obj->has_archive
					? $post_type_obj->rewrite['slug']
					: $post_type_obj->has_archive;

				// Check for rewrite rules.
				$rewrite_rules = get_option( 'rewrite_rules', array() );
				$has_archive_rule = false;

				if ( is_array( $rewrite_rules ) ) {
					foreach ( $rewrite_rules as $pattern => $replacement ) {
						if ( strpos( $pattern, $archive_slug ) !== false &&
							 strpos( $replacement, "post_type={$post_type}" ) !== false ) {
							$has_archive_rule = true;
							break;
						}
					}
				}

				if ( ! $has_archive_rule ) {
					$cpt_issues[] = __( 'Archive enabled but rewrite rules missing - flush permalinks needed', 'wpshadow' );
				}

				// Check if archive URL is accessible.
				$archive_url = get_post_type_archive_link( $post_type );

				if ( ! $archive_url ) {
					$cpt_issues[] = __( 'Archive URL cannot be generated', 'wpshadow' );
				} else {
					// Check for slug conflicts with pages.
					$slug = is_string( $post_type_obj->has_archive )
						? $post_type_obj->has_archive
						: ( isset( $post_type_obj->rewrite['slug'] ) ? $post_type_obj->rewrite['slug'] : $post_type );

					$conflict = get_page_by_path( $slug, OBJECT, 'page' );

					if ( $conflict ) {
						$cpt_issues[] = sprintf(
							/* translators: 1: archive slug, 2: conflicting page ID */
							__( 'Archive slug "%1$s" conflicts with page ID %2$d', 'wpshadow' ),
							$slug,
							$conflict->ID
						);
					}
				}

				// Check if theme supports post type archives.
				$archive_template = locate_template(
					array(
						"archive-{$post_type}.php",
						'archive.php',
					)
				);

				if ( ! $archive_template ) {
					$cpt_issues[] = __( 'No archive template found in theme (will use default)', 'wpshadow' );
				}
			}

			// Check if publicly_queryable is disabled (breaks archives).
			if ( ! $post_type_obj->publicly_queryable ) {
				$cpt_issues[] = __( 'Not publicly queryable - archives cannot be accessed', 'wpshadow' );
			}

			if ( ! empty( $cpt_issues ) ) {
				$problematic_cpts[ $post_type ] = array(
					'label'       => $post_type_obj->label,
					'has_archive' => $post_type_obj->has_archive,
					'posts'       => $total_posts,
					'issues'      => $cpt_issues,
					'archive_url' => get_post_type_archive_link( $post_type ),
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
					'Found archive issues in %d custom post type: ',
					'Found archive issues in %d custom post types: ',
					count( $problematic_cpts ),
					'wpshadow'
				) . implode( ' ', $issues ),
				number_format_i18n( count( $problematic_cpts ) )
			),
			'severity'    => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cpt-archive-support',
			'details'     => array(
				'problematic_cpts' => $problematic_cpts,
			),
		);
	}
}
