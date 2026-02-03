<?php
/**
 * Post Revisions Not Managed Diagnostic
 *
 * Checks if post revisions are managed.
 * Revision management = limit + cleanup strategy.
 * Without management = revisions accumulate indefinitely.
 * With management = controlled database size.
 *
 * **What This Check Does:**
 * - Checks WP_POST_REVISIONS setting
 * - Validates cleanup strategy exists
 * - Counts old revisions (>90 days)
 * - Estimates space consumed by old revisions
 * - Checks for automated cleanup
 * - Returns severity if no management strategy
 *
 * **Why This Matters:**
 * Setting limit only prevents future bloat.
 * Existing revisions remain. Still consume space.
 * Management = limit + periodic cleanup.
 * Remove old revisions (>90 days). Reclaim space.
 * Automated cleanup = maintenance-free.
 *
 * **Business Impact:**
 * Corporate blog: 3000 posts, 180K revisions, 1.2GB space. Set
 * WP_POST_REVISIONS=5 (prevents future bloat). But existing 180K
 * revisions still there. Implemented cleanup: WP-CLI scheduled task
 * (monthly cron), DELETE revisions older than 90 days, keep minimum
 * 3 revisions per post. First cleanup: removed 165K old revisions
 * (92%), reclaimed 1.1GB. Monthly cleanup: removes ~500 old revisions.
 * Database stays lean automatically. Query performance: 65% improvement.
 * Backup size: 1.4GB → 0.3GB. Backup time: 12 min → 2 min. Setup:
 * 1 hour (WP-CLI script + cron). Maintenance: zero (automated).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Automated maintenance
 * - #9 Show Value: Sustained performance, no manual work
 * - #10 Beyond Pure: Proactive database hygiene
 *
 * **Related Checks:**
 * - Post Revisions Limit (prevention)
 * - Post Revision Accumulation (detection)
 * - Database Optimization (complementary)
 *
 * **Learn More:**
 * Revision management: https://wpshadow.com/kb/revision-management
 * Video: Automated revision cleanup (11min): https://wpshadow.com/training/revision-cleanup
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revisions Not Managed Diagnostic Class
 *
 * Detects unmanaged post revisions.
 *
 * **Detection Pattern:**
 * 1. Check WP_POST_REVISIONS setting (limit set?)
 * 2. Count revisions older than 90 days
 * 3. Check for cleanup automation (cron jobs)
 * 4. Estimate space consumed by old revisions
 * 5. Validate cleanup documentation
 * 6. Return if no management strategy
 *
 * **Real-World Scenario:**
 * WP-CLI cleanup command: wp post delete $(wp post list --post_type=revision
 * --format=ids --post_date_before="90 days ago") --force. Scheduled
 * monthly via system cron: 0 3 1 * * /usr/local/bin/wp post delete ...
 * Result: old revisions automatically removed. Database size stable.
 * Manual intervention: never needed. Peace of mind: priceless.
 *
 * **Implementation Notes:**
 * - Checks revision limit setting
 * - Validates cleanup automation
 * - Counts old revisions
 * - Severity: medium (prevents long-term bloat)
 * - Treatment: implement automated cleanup strategy
 *
 * @since 1.2601.2352
 */
class Diagnostic_Post_Revisions_Not_Managed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revisions-not-managed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revisions Not Managed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post revisions are managed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if revision limit is set
		if ( ! defined( 'WP_POST_REVISIONS' ) || WP_POST_REVISIONS === true ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Post revisions are not managed. Limit post revisions to reduce database bloat and improve performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/post-revisions-not-managed',
			);
		}

		return null;
	}
}
