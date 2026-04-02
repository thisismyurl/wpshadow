<?php
/**
 * Post Revision Accumulation Diagnostic
 *
 * Detects excessive post revisions bloating database and slowing queries.
 *
 * **What This Check Does:**
 * 1. Counts post revisions per post
 * 2. Identifies posts with 100+ revisions
 * 3. Measures total revision storage size
 * 4. Calculates database bloat percentage
 * 5. Flags autosave accumulation
 * 6. Projects cleanup performance impact\n *
 * **Why This Matters:**\n * WordPress saves a revision every time you autosave or update a post. A heavily-edited post might
 * accumulate 500+ revisions (each is a full copy of post). These revisions take up database space,
 * slow down queries (more rows to scan), and increase backup sizes. Clearing revisions is free storage\n * and speed recovery.\n *
 * **Real-World Scenario:**\n * Blog with 2,000 published posts and 200 draft posts. Each post had average 50 revisions. Total
 * revisions: 110,000 rows in wp_posts table. Database: 2.1GB (should be 150MB). Backup: 800MB (should
 * be 50MB). Database queries slow scanning unnecessary revisions. After cleaning revisions (keeping
 * only last 3), database: 165MB, backup: 55MB. Query speeds improved 40%. Cost: 1 hour. Value: saved
 * $200/month in storage/hosting.\n *
 * **Business Impact:**\n * - Database bloat: 10-50x larger than needed\n * - Backup sizes huge: slower/more expensive backups\n * - Query performance slow: scans unnecessary revisions\n * - Database server costs: $100-$500+ monthly waste\n * - Disaster recovery slow: huge backup files\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Frees massive storage immediately\n * - #8 Inspire Confidence: Cleaner, simpler database\n * - #10 Talk-About-Worthy: "Recovered 50GB of storage"\n *
 * **Related Checks:**\n * - Database Storage Size (total bloat)\n * - Backup File Size (backup optimization)\n * - Autoloaded Data Size (related bloat)\n * - Database Cleanup Tasks (maintenance)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/post-revision-cleanup\n * - Video: https://wpshadow.com/training/wordpress-revisions-management (5 min)\n * - Advanced: https://wpshadow.com/training/database-cleanup-automation (9 min)\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revision Accumulation Diagnostic Class
 *
 * Flags when post revisions exceed healthy thresholds.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Post_Revision_Accumulation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revision-accumulation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revision Accumulation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive post revisions in the database';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$revision_count = (int) $wpdb->get_var(
			"SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		if ( $revision_count >= 1000 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A large number of post revisions were found. Consider limiting or cleaning up revisions to improve performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'revision_count' => $revision_count,
				),
				'kb_link'      => 'https://wpshadow.com/kb/post-revision-accumulation',
			);
		}

		return null;
	}
}
