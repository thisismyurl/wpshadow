<?php
/**
 * Orphaned Comments Diagnostic
 *
 * Detects orphaned comments (whose parent post no longer exists) and
 * undeleted spam comments that are adding unnecessary database bloat.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Orphaned_Comments Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Orphaned_Comments extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-comments';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Comments';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for orphaned comments whose parent posts have been deleted and undeleted spam comments that are adding unnecessary database bloat.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Counts comments whose comment_post_ID does not correspond to any existing
	 * post, and spam comments that have not been deleted. Either condition beyond
	 * the defined thresholds generates a finding.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		global $wpdb;

		$orphaned_count = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			 FROM {$wpdb->comments} c
			 LEFT JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID
			 WHERE p.ID IS NULL"
		);

		$spam_count = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			 FROM {$wpdb->comments}
			 WHERE comment_approved = 'spam'"
		);

		$issues = array();
		if ( $orphaned_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned comments */
				_n( '%d orphaned comment (post deleted)', '%d orphaned comments (post deleted)', $orphaned_count, 'wpshadow' ),
				$orphaned_count
			);
		}
		if ( $spam_count > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of spam comments */
				_n( '%d undeleted spam comment', '%d undeleted spam comments', $spam_count, 'wpshadow' ),
				$spam_count
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: issue list */
				__( 'Comment table bloat was detected: %s. These rows add overhead to comment queries and waste database storage. Delete spam comments via Comments → Spam in wp-admin and remove orphaned comments with WP-Optimize or WP-CLI.', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'low',
			'threat_level' => 15,
			'details'      => array(
				'orphaned_comments' => $orphaned_count,
				'spam_comments'     => $spam_count,
			),
		);
	}
}
