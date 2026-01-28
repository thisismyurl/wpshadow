<?php
/**
 * Duplicate Content Detection Diagnostic
 *
 * Scans for duplicate page titles, meta descriptions, and content across site.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicate Content Detection Class
 *
 * Tests for duplicate content that could harm SEO.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Duplicate_Content_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-content-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Content Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Scans for duplicate page titles, meta descriptions, and content across site';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for duplicate titles.
		$duplicate_titles = self::find_duplicate_titles();
		if ( $duplicate_titles > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicate titles */
				__( '%d pages have duplicate titles (confuses search engines)', 'wpshadow' ),
				$duplicate_titles
			);
		}

		// Check for duplicate meta descriptions (if SEO plugin active).
		$duplicate_descriptions = self::find_duplicate_meta_descriptions();
		if ( $duplicate_descriptions > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicate descriptions */
				__( '%d pages have duplicate meta descriptions', 'wpshadow' ),
				$duplicate_descriptions
			);
		}

		// Check for missing canonical URLs.
		if ( ! self::has_canonical_support() ) {
			$issues[] = __( 'Site missing canonical URL support (can cause duplicate content issues)', 'wpshadow' );
		}

		// Check pagination implementation.
		if ( ! self::has_proper_pagination() ) {
			$issues[] = __( 'Pagination missing rel=next/prev tags (duplicate content risk)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/duplicate-content-detection',
				'meta'         => array(
					'duplicate_titles'       => $duplicate_titles,
					'duplicate_descriptions' => $duplicate_descriptions,
					'has_canonical'          => self::has_canonical_support(),
					'has_pagination_tags'    => self::has_proper_pagination(),
					'issues_found'           => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Find posts/pages with duplicate titles.
	 *
	 * @since  1.26028.1905
	 * @return int Number of duplicates found.
	 */
	private static function find_duplicate_titles() {
		global $wpdb;

		// Find titles that appear more than once.
		$query = "SELECT COUNT(*) as duplicates
				  FROM (
					  SELECT post_title
					  FROM {$wpdb->posts}
					  WHERE post_status = 'publish'
					  AND post_type IN ('post', 'page')
					  GROUP BY post_title
					  HAVING COUNT(*) > 1
				  ) as dup_titles";

		$duplicates = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return (int) $duplicates;
	}

	/**
	 * Find pages with duplicate meta descriptions.
	 *
	 * @since  1.26028.1905
	 * @return int Number of duplicates found.
	 */
	private static function find_duplicate_meta_descriptions() {
		global $wpdb;

		// Check if Yoast SEO or Rank Math is active.
		$meta_key = false;
		
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			$meta_key = '_yoast_wpseo_metadesc';
		} elseif ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			$meta_key = 'rank_math_description';
		} elseif ( is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) {
			$meta_key = '_aioseop_description';
		}

		if ( ! $meta_key ) {
			return 0; // No SEO plugin, can't check.
		}

		// Find meta descriptions that appear more than once.
		$query = $wpdb->prepare(
			"SELECT COUNT(*) as duplicates
			FROM (
				SELECT meta_value
				FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				AND meta_value != ''
				GROUP BY meta_value
				HAVING COUNT(*) > 1
			) as dup_meta",
			$meta_key
		);

		$duplicates = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return (int) $duplicates;
	}

	/**
	 * Check if site has canonical URL support.
	 *
	 * @since  1.26028.1905
	 * @return bool True if canonical support detected.
	 */
	private static function has_canonical_support() {
		// WordPress core adds canonical since 2.9.
		// Check if it's disabled.
		if ( has_action( 'wp_head', 'rel_canonical' ) === false ) {
			return false;
		}

		// Check if SEO plugin is handling it.
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ||
			 is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ||
			 is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) {
			return true;
		}

		return true; // WordPress default.
	}

	/**
	 * Check if site implements proper pagination.
	 *
	 * @since  1.26028.1905
	 * @return bool True if pagination tags detected.
	 */
	private static function has_proper_pagination() {
		// Check if SEO plugin handles pagination.
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ||
			 is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			return true; // These plugins handle rel=next/prev.
		}

		// Check for theme support (WordPress doesn't add these by default).
		// We can't easily test without loading a paginated page.
		// Assume it's an issue if no SEO plugin is present.
		return false;
	}
}
