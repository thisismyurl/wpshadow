<?php
/**
 * Orphaned User Meta Cleanup Treatment
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
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orphaned User Meta Cleanup Treatment Class
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
 * @since 1.6093.1200
 */
class Treatment_Orphaned_User_Meta_Cleanup extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-user-meta-cleanup';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned User Meta Cleanup';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for user metadata from deleted users';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Orphaned_User_Meta_Cleanup' );
	}
}
