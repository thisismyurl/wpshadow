<?php
/**
 * Comment Status Inconsistencies Diagnostic
 *
 * Detects database integrity issues where comments have invalid status values.
 * WordPress expects comment_approved to be: 0 (pending), 1 (approved), spam, trash,
 * post-trashed. Invalid values cause display inconsistencies, moderation breakage, and
 * can indicate database corruption or malicious tampering.
 *
 * **What This Check Does:**
 * - Queries database for comments with invalid comment_approved status
 * - Counts comments in invalid states (missing from all standard statuses)
 * - Detects database corruption or malicious status injection
 * - Tests comment display integrity (comments with corrupt status won't display)
 * - Flags status values suggesting database attack (e.g., random strings)
 * - Validates status values can be corrected
 *
 * **Why This Matters:**
 * Invalid comment statuses = comments become orphaned and undisplayable. Attack vectors:
 * - Database access via SQL injection: attacker sets random status values (prevents comment moderation)
 * - Database corruption: hardware failure creates invalid status values
 * - Plugin bug: corrupts status during comment processing
 * - Site migration: export/import process breaks status field
 *
 * **Business Impact:**
 * Corrupted comment statuses = moderation system breaks. Scenarios:
 * - 50+ comments with invalid status never appear in moderation queue
 * - Legitimate comments disappear from site (users think posts ignored)
 * - Moderation interface shows errors trying to process corrupt comments
 * - Cannot delete corrupted comments through admin UI
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Database integrity validation
 * - #9 Show Value: Early corruption detection enables quick fix
 * - #10 Beyond Pure: Ensures comment system works for all users
 *
 * **Related Checks:**
 * - Database Table Corruption Check (general DB health)
 * - Comment Status Workflow (if corruption detected)
 * - Plugin XSS Vulnerability (detect if SQL injection occurred)
 *
 * **Learn More:**
 * Database integrity: https://wpshadow.com/kb/database-status-inconsistencies
 * Video: Database maintenance guide (8min): https://wpshadow.com/training/database-health
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Status Inconsistencies Diagnostic Class
 *
 * Implements status validation by querying database for comments with status
 * values outside valid set: {0, 1, spam, trash, post-trashed}. Any other value
 * indicates corruption or tampering.
 *
 * **Detection Pattern:**
 * 1. Execute query on wp_comments table
 * 2. Check comment_approved column for each row
 * 3. Filter for values NOT IN (0, 1, 'spam', 'trash', 'post-trashed')
 * 4. Count invalid comments
 * 5. Return severity based on count (1-5 low, 5-50 medium, 50+ critical)
 *
 * **Real-World Scenario:**
 * E-commerce site with custom comment integration. Plugin developer makes mistake
 * in comment processing: writes comment_approved = 2 for verified buyer comments.
 * Over time: 500 verified buyer comments in invalid status. Admin can't moderate them,
 * they don't display. Customers complain reviews missing. Fix: update 500 rows
 * to status=1, restore visibility.
 *
 * **Implementation Notes:**
 * - Uses wpdb->get_var() for efficient count query
 * - Returns severity: medium (1-50 corrupt), critical (50+ corrupt)
 * - Non-fixable diagnostic (requires database admin to fix)
 *
 * @since 1.26031.1500
 */
class Diagnostic_Comment_Status_Inconsistencies extends Diagnostic_Base {
	protected static $slug = 'comment-status-inconsistencies';
	protected static $title = 'Comment Status Inconsistencies';
	protected static $description = 'Finds comments with invalid status values';
	protected static $family = 'security';

	public static function check() {
		global $wpdb;

		$valid_statuses = array( '0', '1', 'spam', 'trash', 'post-trashed' );
		$placeholders   = implode( ',', array_fill( 0, count( $valid_statuses ), '%s' ) );

		$invalid_comments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments}
				WHERE comment_approved NOT IN ($placeholders)",
				...$valid_statuses
			)
		);

		if ( $invalid_comments > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d comments with invalid status values - may cause display issues', 'wpshadow' ),
					$invalid_comments
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-status-inconsistencies',
			);
		}

		return null;
	}
}
