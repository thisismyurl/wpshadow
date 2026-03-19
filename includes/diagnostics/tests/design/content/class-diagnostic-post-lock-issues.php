<?php
/**
 * Post Lock Issues Diagnostic
 *
 * Detects posts stuck in editing lock state. Identifies lock timeouts
 * and concurrent editing conflicts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Lock Issues Diagnostic Class
 *
 * Checks for posts stuck in editing lock state that prevent
 * other users from editing or cause confusion.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Post_Lock_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-lock-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Lock Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects posts stuck in editing lock state and concurrent editing conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for stale post locks (>2 hours old, default lock time is 150 seconds).
		$stale_locks = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, p.post_title, pm.meta_value
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE pm.meta_key = '_edit_lock'
				AND pm.meta_value < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 HOUR))
				AND p.post_status IN ('draft', 'pending', 'publish', 'private')
				LIMIT 10"
			)
		);

		if ( ! empty( $stale_locks ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have stale editing locks (>2 hours old)', 'wpshadow' ),
				count( $stale_locks )
			);
		}

		// Check for posts with multiple concurrent locks (shouldn't happen).
		$duplicate_locks = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id)
			FROM (
				SELECT post_id, COUNT(*) as lock_count
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_edit_lock'
				GROUP BY post_id
				HAVING lock_count > 1
			) as duplicate_locks_subquery"
		);

		if ( $duplicate_locks > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have duplicate editing locks', 'wpshadow' ),
				$duplicate_locks
			);
		}

		// Check for locks with invalid user IDs.
		$invalid_user_locks = $wpdb->get_var(
			"SELECT COUNT(pm.post_id)
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->users} u ON CAST(SUBSTRING_INDEX(pm.meta_value, ':', 1) AS UNSIGNED) = u.ID
			WHERE pm.meta_key = '_edit_lock'
			AND u.ID IS NULL"
		);

		if ( $invalid_user_locks > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d post locks reference non-existent users', 'wpshadow' ),
				$invalid_user_locks
			);
		}

		// Check if Heartbeat API is disabled (required for lock management).
		if ( defined( 'WP_ADMIN_HEARTBEAT_DISABLE' ) && WP_ADMIN_HEARTBEAT_DISABLE ) {
			$issues[] = __( 'Heartbeat API disabled (required for post lock management)', 'wpshadow' );
		}

		// Check for very high number of active locks (might indicate issues).
		$total_active_locks = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_edit_lock'
				AND meta_value > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 MINUTE))"
			)
		);

		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status IN ('draft', 'pending', 'publish', 'private')
			AND post_type IN ('post', 'page')"
		);

		$lock_percentage = $total_posts > 0 ? ( $total_active_locks / $total_posts ) * 100 : 0;

		if ( $lock_percentage > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: percentage of posts */
				__( '%d%% of posts currently have editing locks (unusually high)', 'wpshadow' ),
				round( $lock_percentage )
			);
		}

		// Check for posts with last modified time BEFORE their lock time (indicates stuck state).
		$stuck_locks = $wpdb->get_var(
			"SELECT COUNT(p.ID)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE pm.meta_key = '_edit_lock'
			AND UNIX_TIMESTAMP(p.post_modified) < CAST(pm.meta_value AS UNSIGNED) - 3600"
		);

		if ( $stuck_locks > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts may be stuck in locked state', 'wpshadow' ),
				$stuck_locks
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-lock-issues',
			);
		}

		return null;
	}
}
