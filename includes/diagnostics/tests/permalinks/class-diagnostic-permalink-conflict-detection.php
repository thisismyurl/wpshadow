<?php
/**
 * Permalink Conflict Detection Diagnostic
 *
 * Detects conflicts between post slugs, pages, and custom post types that could
 * cause permalink collisions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1745
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Conflict Detection Diagnostic Class
 *
 * Identifies potential URL conflicts in WordPress permalink structure.
 *
 * @since 1.26032.1745
 */
class Diagnostic_Permalink_Conflict_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-conflict-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Conflict Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects URL conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1745
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for page slugs conflicting with reserved terms.
		$reserved_terms = array(
			'admin', 'wp-admin', 'wp-content', 'wp-includes', 'feed', 'rss', 'rss2',
			'atom', 'rdf', 'trackback', 'comments', 'search', 'category', 'tag',
			'author', 'page', 'tag', 'attachment', 'xmlrpc', 'login', 'logout',
		);

		$conflicting_pages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_name, post_title FROM {$wpdb->posts} 
				WHERE post_type = 'page' 
				AND post_status = 'publish'
				AND post_name IN (" . implode( ',', array_fill( 0, count( $reserved_terms ), '%s' ) ) . ')',
				$reserved_terms
			)
		);

		if ( ! empty( $conflicting_pages ) ) {
			$conflict_names = array_map(
				function( $page ) {
					return $page->post_title;
				},
				$conflicting_pages
			);

			$issues[] = sprintf(
				/* translators: %s: comma-separated list of page titles */
				__( 'Pages using reserved WordPress slugs found: %s', 'wpshadow' ),
				implode( ', ', $conflict_names )
			);
		}

		// Check for custom post type conflicts.
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$slugs      = array();

		foreach ( $post_types as $post_type ) {
			if ( isset( $post_type->rewrite['slug'] ) ) {
				$slug = $post_type->rewrite['slug'];
				if ( isset( $slugs[ $slug ] ) ) {
					$issues[] = sprintf(
						/* translators: 1: post type 1, 2: post type 2, 3: slug */
						__( 'Post types "%1$s" and "%2$s" both use slug "%3$s"', 'wpshadow' ),
						$slugs[ $slug ],
						$post_type->label,
						$slug
					);
				}
				$slugs[ $slug ] = $post_type->label;
			}
		}

		// Check if page slug matches post type slug.
		foreach ( $slugs as $slug => $label ) {
			$page_with_slug = get_page_by_path( $slug );
			if ( $page_with_slug ) {
				$issues[] = sprintf(
					/* translators: 1: page title, 2: post type label */
					__( 'Page "%1$s" slug conflicts with post type "%2$s"', 'wpshadow' ),
					$page_with_slug->post_title,
					$label
				);
			}
		}

		// Check for posts/pages with identical slugs.
		$duplicate_slugs = $wpdb->get_results(
			"SELECT post_name, GROUP_CONCAT(post_type) as post_types, COUNT(*) as count
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')
			GROUP BY post_name
			HAVING count > 1
			LIMIT 10"
		);

		if ( ! empty( $duplicate_slugs ) ) {
			foreach ( $duplicate_slugs as $duplicate ) {
				$issues[] = sprintf(
					/* translators: 1: slug name, 2: post types */
					__( 'Duplicate slug "%1$s" used by: %2$s', 'wpshadow' ),
					$duplicate->post_name,
					$duplicate->post_types
				);
			}
		}

		// Check if category base conflicts with page slugs.
		$category_base = get_option( 'category_base', 'category' );
		if ( empty( $category_base ) ) {
			$category_base = 'category';
		}

		$page_with_cat_slug = get_page_by_path( $category_base );
		if ( $page_with_cat_slug ) {
			$issues[] = sprintf(
				/* translators: 1: page title, 2: category base */
				__( 'Page "%1$s" slug conflicts with category base "%2$s"', 'wpshadow' ),
				$page_with_cat_slug->post_title,
				$category_base
			);
		}

		// Check if tag base conflicts with page slugs.
		$tag_base = get_option( 'tag_base', 'tag' );
		if ( empty( $tag_base ) ) {
			$tag_base = 'tag';
		}

		$page_with_tag_slug = get_page_by_path( $tag_base );
		if ( $page_with_tag_slug ) {
			$issues[] = sprintf(
				/* translators: 1: page title, 2: tag base */
				__( 'Page "%1$s" slug conflicts with tag base "%2$s"', 'wpshadow' ),
				$page_with_tag_slug->post_title,
				$tag_base
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/permalink-conflict-detection',
			);
		}

		return null;
	}
}
