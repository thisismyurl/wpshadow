<?php
/**
 * ORDER BY Query Performance Diagnostic
 *
 * Detects ORDER BY clauses that cause expensive file sorts.
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
 * Diagnostic_Order_By_Performance Class
 *
 * Identifies ORDER BY queries that require file sort or filesort operations.
 */
class Diagnostic_Order_By_Performance extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'order-by-performance';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'ORDER BY Query Performance';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for ORDER BY clauses causing expensive filesorts';

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

		$issues = array();

		// Check for posts with many revisions (ORDER BY gets expensive)
		$high_revision_posts = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_parent) FROM {$wpdb->posts}
			WHERE post_type = 'revision'
			GROUP BY post_parent
			HAVING COUNT(*) > 100"
		);

		if ( $high_revision_posts > 0 ) {
			$issues[] = __( 'Posts with 100+ revisions detected. Ordering revision queries is expensive.', 'wpshadow' );
		}

		// Check for ordering by post_title (varchar field - slow)
		$posts_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" );
		if ( $posts_count > 10000 ) {
			$issues[] = __( 'Large post count (10,000+). Ordering by post_title or post_content is inefficient.', 'wpshadow' );
		}

		// Check for meta queries with ORDER BY
		$meta_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta}" );
		if ( $meta_count > 100000 ) {
			$issues[] = sprintf(
				/* translators: %d: count of meta entries */
				__( '%d post meta entries. ORDER BY meta queries will use filesort.', 'wpshadow' ),
				$meta_count
			);
		}

		// Check for comments with high ordering activity
		$comment_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments}" );
		if ( $comment_count > 50000 ) {
			$issues[] = sprintf(
				/* translators: %d: comment count */
				__( '%d comments. Ordering comment queries is expensive.', 'wpshadow' ),
				$comment_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'      => array(
					'posts_with_high_revisions' => $high_revision_posts ?? 0,
					'total_posts'               => $posts_count ?? 0,
					'total_meta_entries'        => $meta_count ?? 0,
					'total_comments'            => $comment_count ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/order-by-performance',
			);
		}

		return null;
	}
}
