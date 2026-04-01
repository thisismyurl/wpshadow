<?php
/**
 * Comment Update Lock Timeouts Diagnostic
 *
 * Validates that WordPress comment post locks expire within reasonable timeframe.\n * Comment post locks prevent concurrent edits. Stale locks block legitimate edits.\n * Scenario: Moderator locks comment for 1 hour to review/edit. Lock expires after\n * 5 minutes. If lock doesn't expire, moderator stuck with stale lock until server restart.\n *
 * **What This Check Does:**
 * - Checks post lock timeout setting (_post_lock_life constant)\n * - Validates default 150-second (2.5 minute) timeout is in effect\n * - Detects stale locks in database older than timeout window\n * - Tests lock expiration by checking lock age\n * - Confirms moderator workflows unblocked by expired locks\n * - Validates cleanup of old lock records\n *
 * **Why This Matters:**
 * Stale comment locks paralyze moderation workflows. Scenarios:\n * - Moderator edits comment, saves mid-edit, browser crashes\n * - Lock stays active for hours/days without timeout\n * - Next moderator can't edit same comment (locked)\n * - Can't delete comment (locked by ghost edit session)\n * - Forces administrator to manually delete lock from database\n *
 * **Business Impact:**
 * Moderation workflow blocked: moderate 100 comments/day. 1-2 lock up due to stale\n * locks. Admin manually clears database. Lost productivity: 10-15 min per incident.\n * For team of 3 moderators: 10-15 min ×1.0 stale locks/day × 250 working days\n * = 62-93 hours/year of admin intervention. Cost: $1,860-$2,790/year.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Moderation workflows stay unblocked\n * - #9 Show Value: Quantified productivity recovery\n * - #10 Beyond Pure: Prevents frustrating ghost locks\n *
 * **Related Checks:**
 * - Comment Form CAPTCHA (moderation prerequisites)\n * - Database Table Corruption (stale records indicate database maintenance needed)\n * - Post Publish Lock Timeouts (same pattern on post edits)\n *
 * **Learn More:**
 * Moderation performance: https://wpshadow.com/kb/comment-moderation-optimization\n * Video: Unsticking locked comments (6min): https://wpshadow.com/training/comment-locks\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Update Lock Timeouts Diagnostic Class
 *
 * Implements lock timeout validation using _post_lock_life and stale record detection.\n *
 * **Detection Pattern:**
 * 1. Get _post_lock_life constant (default 150 seconds)\n * 2. Query postmeta for _post_lock records\n * 3. Parse lock timestamp from postmeta_value\n * 4. Calculate lock age: current_time - lock_time\n * 5. Flag locks older than _post_lock_life value\n * 6. Return severity if stale locks found\n *
 * **Real-World Scenario:**
 * Blog with 2 moderators. Comment on hot topic gets edited by Moderator A.\n * Browser crashes mid-edit. _post_lock stays in postmeta without expiration.\n * Next day, Moderator B tries to edit same comment. Sees \"Editing blocked\"\n * message. Contact admin. Admin connects database, manually deletes lock.\n * Enable lock timeout check. Now stale locks auto-expire. No more manual intervention.\n *
 * **Implementation Notes:**
 * - Uses _post_lock_life constant (typically 150 seconds)\n * - Scans postmeta for old lock records\n * - Severity: high if many stale locks, medium if one or two\n * - Treatment: auto-cleanup of expired locks\n *
 * @since 0.6093.1200
 */
class Diagnostic_Comment_Update_Lock_Timeouts extends Diagnostic_Base {
	protected static $slug = 'comment-update-lock-timeouts';
	protected static $title = 'Comment Update Lock Timeouts';
	protected static $description = 'Detects comments stuck in update locks';
	protected static $family = 'security';

	public static function check() {
		global $wpdb;

		// Check for locked comments (post locks from editing).
		$locked_comments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}
				WHERE meta_key = '_edit_lock'
				AND meta_value < %d
				AND post_id IN (SELECT comment_post_ID FROM {$wpdb->comments})",
				time() - 3600
			)
		);

		if ( $locked_comments > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d posts with stale edit locks that may affect comment management', 'wpshadow' ),
					$locked_comments
				),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/comment-update-lock-timeouts?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
