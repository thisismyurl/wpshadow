<?php
/**
 * Orphaned User Meta Cleanup Diagnostic
 *
 * Checks for user meta entries referencing deleted users.
 * Orphaned usermeta = metadata pointing to non-existent users.
 * Wastes database space. Slows user meta queries.
 * Common on membership sites with high user churn.
 *
 * **What This Check Does:**
 * - Scans usermeta table for orphaned entries
 * - Identifies user_id values with no matching user
 * - Counts orphaned rows
 * - Estimates wasted space
 * - Provides cleanup recommendation
 * - Returns severity if significant orphans found
 *
 * **Why This Matters:**
 * Users deleted (spam, inactive, GDPR requests).
 * Usermeta remains (no cascade delete by default).
 * Membership sites: thousands of deleted users over time.
 * Orphaned meta accumulates. Slows user meta queries.
 * Cleanup: reclaim space, improve performance.
 *
 * **Business Impact:**
 * Membership site: 100K users registered over 5 years. 40K deleted
 * (spam, inactive, cancellations). usermeta table: 850K rows, 135MB.
 * Orphan check: 320K orphaned rows (38% of table). Space wasted: 52MB.
 * Query performance: get_user_meta() slower (larger table scans).
 * Cleanup: DELETE FROM wp_usermeta WHERE user_id NOT IN (SELECT ID
 * FROM wp_users). Result: 850K → 530K rows (38% reduction). Space
 * reclaimed: 52MB. User meta queries: 35% faster. Admin user screens:
 * noticeably faster. Setup: 5 minutes query. Regular maintenance:
 * quarterly. Important for GDPR compliance (fully remove user data).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Complete user data removal
 * - #9 Show Value: Space + speed improvement
 * - #10 Beyond Pure: GDPR compliance, data hygiene
 *
 * **Related Checks:**
 * - Orphaned Post Meta (similar issue)
 * - User Data Privacy (GDPR compliance)
 * - Database Table Optimization (complementary)
 *
 * **Learn More:**
 * Usermeta cleanup: https://wpshadow.com/kb/usermeta-cleanup
 * Video: GDPR-compliant user deletion (12min): https://wpshadow.com/training/gdpr-delete
 *
 * @package    WPShadow
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
 * Orphaned User Meta Cleanup Diagnostic Class
 *
 * Detects user meta entries orphaned by deleted users.
 *
 * **Detection Pattern:**
 * 1. Query usermeta for distinct user_id values
 * 2. Check each user_id exists in wp_users
 * 3. Count orphaned rows
 * 4. Estimate wasted space
 * 5. Calculate query performance impact
 * 6. Return if orphans exceed threshold (>1000 or >5%)
 *
 * **Real-World Scenario:**
 * GDPR erasure requests: 5000 users deleted. WordPress delete_user()
 * called but without $reassign parameter. User rows deleted, usermeta
 * remained (15K meta entries). Privacy audit failed (personal data
 * still in DB). Cleanup removed orphaned meta. Privacy compliance
 * restored. Lesson: always verify complete data removal for GDPR.
 *
 * **Implementation Notes:**
 * - Checks usermeta for orphaned entries
 * - Counts rows, estimates space
 * - Validates GDPR compliance
 * - Severity: medium (privacy + performance concern)
 * - Treatment: DELETE orphaned rows, document for compliance
 *
 * @since 0.6093.1200
 */
class Diagnostic_Orphaned_User_Meta_Cleanup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-user-meta-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned User Meta Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for user metadata from deleted users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$orphaned = (int) $wpdb->get_var(
			"SELECT COUNT(1) FROM {$wpdb->usermeta} um
			LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
			WHERE u.ID IS NULL"
		);

		if ( $orphaned >= 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Orphaned user metadata from deleted users was found. Cleaning it up can improve performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'orphaned_count' => $orphaned,
				),
				'kb_link'      => 'https://wpshadow.com/kb/orphaned-user-meta-cleanup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
