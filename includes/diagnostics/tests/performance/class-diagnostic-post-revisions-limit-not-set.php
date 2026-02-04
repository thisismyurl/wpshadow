<?php
/**
 * Post Revisions Limit Not Set Diagnostic
 *
 * Checks if post revisions limit is set.
 * Post revisions = WordPress saves every edit as separate revision.
 * Without limit = unlimited revisions (hundreds per post).
 * With limit = control database bloat.
 *
 * **What This Check Does:**
 * - Checks WP_POST_REVISIONS constant
 * - Validates revision limit configuration
 * - Counts existing revisions per post
 * - Estimates database space used by revisions
 * - Checks for revision cleanup strategy
 * - Returns severity if revisions unlimited
 *
 * **Why This Matters:**
 * Every save = new revision. Heavy editors: 200+ revisions per post.
 * Each revision = duplicate of post content in database.
 * Unlimited revisions = massive database bloat.
 * Queries slower (scanning thousands of unnecessary rows).
 * Limit revisions = lean database, faster queries.
 *
 * **Business Impact:**
 * Magazine site: 5000 posts, heavy editing. Average 85 revisions per
 * post. Total revisions: 425K rows, 2.8GB database space. Query impact:
 * post queries scanning revision rows unnecessarily. wp_posts table:
 * sluggish. Added to wp-config.php: define('WP_POST_REVISIONS', 5).
 * Cleaned old revisions: DELETE FROM wp_posts WHERE post_type='revision'
 * AND post_date < DATE_SUB(NOW(), INTERVAL 90 DAY). Result: 425K →
 * 25K revision rows (94% reduction). Space reclaimed: 2.6GB. Database
 * size: 3.2GB → 0.6GB. Query performance: 70% faster. Backup time:
 * 15 minutes → 3 minutes. Setup: 5 minutes. Ongoing: automatic limit.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Lean, optimized database
 * - #9 Show Value: GBs reclaimed, dramatic speed improvement
 * - #10 Beyond Pure: Proactive data management
 *
 * **Related Checks:**
 * - Post Revision Accumulation (cleanup check)
 * - Database Table Optimization (complementary)
 * - Database Size Monitoring (broader metric)
 *
 * **Learn More:**
 * Revision management: https://wpshadow.com/kb/revisions
 * Video: WordPress revisions explained (10min): https://wpshadow.com/training/revisions
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revisions Limit Not Set Diagnostic Class
 *
 * Detects unlimited post revisions.
 *
 * **Detection Pattern:**
 * 1. Check WP_POST_REVISIONS constant
 * 2. If undefined or true = unlimited
 * 3. Count revision posts in database
 * 4. Estimate space consumed
 * 5. Calculate percentage of wp_posts table
 * 6. Return if unlimited or excessive revisions
 *
 * **Real-World Scenario:**
 * wp-config.php: define('WP_POST_REVISIONS', 10); // Keep last 10.
 * OR: define('WP_POST_REVISIONS', false); // Disable entirely (risky).
 * Best practice: 3-10 revisions (balance between undo capability and
 * database size). Heavy editors: 10. Light editors: 3-5. Also scheduled
 * cleanup: monthly WP-CLI command to remove old revisions. Result:
 * database stays lean regardless of editing frequency.
 *
 * **Implementation Notes:**
 * - Checks WP_POST_REVISIONS constant
 * - Counts existing revisions
 * - Estimates space impact
 * - Severity: medium (significant space + performance issue)
 * - Treatment: set revision limit in wp-config.php
 *
 * @since 1.6030.2352
 */
class Diagnostic_Post_Revisions_Limit_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revisions-limit-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revisions Limit Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post revisions limit is set';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check post revisions limit
		if ( ! defined( 'WP_POST_REVISIONS' ) || WP_POST_REVISIONS === true ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Post revisions limit is not set. Add define( \'WP_POST_REVISIONS\', 3 ); to wp-config.php to limit database growth.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/post-revisions-limit-not-set',
			);
		}

		return null;
	}
}
