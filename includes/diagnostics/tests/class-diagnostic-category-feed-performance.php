<?php
/**
 * Category Feed Performance Diagnostic
 *
 * Analyzes performance impact of category feed generation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category Feed Performance Diagnostic Class
 *
 * Checks performance implications of category feeds.
 *
 * @since 1.26032.1900
 */
class Diagnostic_Category_Feed_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'category-feed-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Category Feed Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes category feed performance impact';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reading';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check number of categories.
		$category_count = wp_count_terms( array( 'taxonomy' => 'category' ) );

		if ( $category_count > 500 ) {
			$issues[] = sprintf(
				/* translators: %d: number of categories */
				__( 'High number of categories (%d) - each generates feed on request', 'wpshadow' ),
				$category_count
			);
		}

		// Check if category feeds are being indexed.
		$blog_public = get_option( 'blog_public', 1 );
		if ( $blog_public && $category_count > 100 ) {
			$issues[] = __( 'Search engines may be crawling all category feeds - high server load', 'wpshadow' );
		}

		// Check for empty categories that generate empty feeds.
		global $wpdb;
		$empty_categories = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->term_taxonomy}
			WHERE taxonomy = 'category' AND count = 0"
		);

		if ( $empty_categories > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of empty categories */
				__( '%d empty categories generate unused feeds', 'wpshadow' ),
				$empty_categories
			);
		}

		// Check if feed pagination is enabled.
		$posts_per_page = (int) get_option( 'posts_per_page', 10 );
		if ( $posts_per_page > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: posts per feed */
				__( 'Feed contains %d posts - large feed size impacts feed readers', 'wpshadow' ),
				$posts_per_page
			);
		}

		// Check if feed excerpts are being used.
		$rss_use_excerpt = get_option( 'rss_use_excerpt', 0 );
		if ( ! $rss_use_excerpt ) {
			$issues[] = __( 'Full post content in feeds increases feed size and server load', 'wpshadow' );
		}

		// Check cache effectiveness for category feeds.
		$cache_enabled = wp_cache_is_enabled();
		if ( ! $cache_enabled && $category_count > 50 ) {
			$issues[] = __( 'Object cache disabled with many category feeds - expect high server load', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/category-feed-performance',
			);
		}

		return null;
	}
}
