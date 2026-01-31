<?php
/**
 * Slow Query Detection Diagnostic
 *
 * Detects patterns that typically result in slow queries.
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
 * Diagnostic_Slow_Query_Detection Class
 *
 * Identifies query patterns that are known to be slow.
 */
class Diagnostic_Slow_Query_Detection extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'slow-query-detection';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Slow Query Detection';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects database patterns that typically cause slow queries';

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

		$warnings = array();

		// Check for subqueries (common in plugin queries)
		$posts_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" );
		if ( $posts_count > 100000 ) {
			$warnings[] = __( 'Large posts table (100K+). Subqueries and nested searches are very slow.', 'wpshadow' );
		}

		// Check for IN clauses with many values
		$taxonomy_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->term_relationships}"
		);

		if ( $taxonomy_count > 1000000 ) {
			$warnings[] = sprintf(
				/* translators: %d: count of term relationships */
				__( '%d term relationships. Large IN clauses in taxonomy queries will cause filesorts.', 'wpshadow' ),
				$taxonomy_count
			);
		}

		// Check for NOT IN clauses (very expensive)
		$orphaned_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type NOT IN ('post', 'page', 'attachment')"
		);

		if ( $orphaned_posts > 10000 ) {
			$warnings[] = sprintf(
				/* translators: %d: count of non-standard post types */
				__( '%d posts with non-standard types. NOT IN queries are very expensive at this volume.', 'wpshadow' ),
				$orphaned_posts
			);
		}

		// Check for DISTINCT queries
		$high_activity = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments}"
		);

		if ( $high_activity > 100000 ) {
			$warnings[] = __( 'Large comments table (100K+). DISTINCT queries on comments are slow.', 'wpshadow' );
		}

		// Check for full table scans (no date filtering)
		$old_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_date < DATE_SUB(NOW(), INTERVAL 5 YEAR)"
		);

		if ( $old_posts > 50000 ) {
			$warnings[] = sprintf(
				/* translators: %d: count of old posts */
				__( '%d posts older than 5 years. Queries without date filtering will full-scan these.', 'wpshadow' ),
				$old_posts
			);
		}

		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'details'      => array(
					'posts_count'               => $posts_count ?? 0,
					'term_relationships_count'  => $taxonomy_count ?? 0,
					'non_standard_post_types'   => $orphaned_posts ?? 0,
					'comments_count'            => $high_activity ?? 0,
					'very_old_posts'            => $old_posts ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/slow-query-detection',
			);
		}

		return null;
	}
}
