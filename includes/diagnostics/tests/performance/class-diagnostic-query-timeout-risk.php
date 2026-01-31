<?php
/**
 * Query Timeout Risk Diagnostic
 *
 * Detects queries likely to timeout under load.
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
 * Diagnostic_Query_Timeout_Risk Class
 *
 * Identifies query patterns that risk timeout under load.
 */
class Diagnostic_Query_Timeout_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-timeout-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Query Timeout Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects queries likely to timeout under site load';

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

		$timeout_risks = array();

		// Check for large batch operations
		$posts_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" );
		if ( $posts_count > 500000 ) {
			$timeout_risks[] = sprintf(
				/* translators: %d: post count */
				__( '%d posts. Bulk operations will timeout at default limits.', 'wpshadow' ),
				$posts_count
			);
		}

		// Check for complex taxonomies
		$terms_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->terms}" );
		if ( $terms_count > 50000 ) {
			$timeout_risks[] = sprintf(
				/* translators: %d: term count */
				__( '%d terms. Getting all terms with descriptions will timeout.', 'wpshadow' ),
				$terms_count
			);
		}

		// Check for recursive meta queries
		$large_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			GROUP BY post_id
			HAVING COUNT(*) > 200"
		);

		if ( $large_meta > 0 ) {
			$timeout_risks[] = __( 'Posts with 200+ meta entries exist. Recursive meta queries will timeout.', 'wpshadow' );
		}

		// Check for orphaned relationships
		$orphaned_relations = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->term_relationships} tr
			LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_relations > 100000 ) {
			$timeout_risks[] = sprintf(
				/* translators: %d: orphaned count */
				__( '%d orphaned term relationships. Queries with LEFT JOINs will timeout.', 'wpshadow' ),
				$orphaned_relations
			);
		}

		// Check for very large comment threads
		$long_threads = $wpdb->get_var(
			"SELECT MAX(comment_count) FROM {$wpdb->posts}"
		);

		if ( $long_threads > 10000 ) {
			$timeout_risks[] = sprintf(
				/* translators: %d: max comments on one post */
				__( 'Post with %d comments. Loading comment thread will timeout.', 'wpshadow' ),
				$long_threads
			);
		}

		if ( ! empty( $timeout_risks ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $timeout_risks ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'details'      => array(
					'posts_count'           => $posts_count ?? 0,
					'terms_count'           => $terms_count ?? 0,
					'posts_with_heavy_meta' => $large_meta ?? 0,
					'orphaned_relationships' => $orphaned_relations ?? 0,
					'max_comments_per_post' => $long_threads ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/query-timeout-risk',
			);
		}

		return null;
	}
}
