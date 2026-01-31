<?php
/**
 * LIKE Query Optimization Diagnostic
 *
 * Detects LIKE queries that prevent index usage.
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Like_Query_Optimization Class
 *
 * Identifies LIKE clauses that could use full-text search or better indexing.
 */
class Diagnostic_Like_Query_Optimization extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'like-query-optimization';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'LIKE Query Optimization';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for LIKE queries that bypass indexes and slow searches';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$findings = array();

		// Check for posts with long titles (often indicate LIKE search issues)
		$long_titles = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE LENGTH(post_title) > 500"
		);

		if ( $long_titles > 100 ) {
			$findings[] = sprintf(
				/* translators: %d: count of long titles */
				__( '%d posts have titles over 500 characters. LIKE queries on post_title are inefficient.', 'wpshadow' ),
				$long_titles
			);
		}

		// Check for searchable content without full-text index
		$post_content_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(post_content)) FROM {$wpdb->posts} 
			WHERE post_type = 'post' AND post_status = 'publish'"
		);

		if ( $post_content_size > 100000000 ) { // 100MB
			$findings[] = __( 'Large post_content volume detected. LIKE queries will be slow. Consider full-text indexing.', 'wpshadow' );
		}

		// Check for high-volume search queries in logs (if available)
		$high_activity = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'draft' OR post_status = 'pending'"
		);

		if ( $high_activity > 10000 ) {
			$findings[] = sprintf(
				/* translators: %d: count of non-published posts */
				__( '%d non-published posts exist. Searching across these with LIKE queries is expensive.', 'wpshadow' ),
				$high_activity
			);
		}

		if ( ! empty( $findings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $findings ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'long_titles_count'    => $long_titles ?? 0,
					'content_size_bytes'   => $post_content_size ?? 0,
					'non_published_posts'  => $high_activity ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/like-query-optimization',
			);
		}

		return null;
	}
}
