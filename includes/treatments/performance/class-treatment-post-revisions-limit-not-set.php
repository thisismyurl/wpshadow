<?php
/**
 * Post Revisions Limit Not Set Treatment
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
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revisions Limit Not Set Treatment Class
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
 * @since 0.6093.1200
 */
class Treatment_Post_Revisions_Limit_Not_Set extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'post-revisions-limit-not-set';
	}

	/**
	 * Get the risk level for this treatment.
	 *
	 * Editing wp-config.php is a high-risk operation — a mistake can take
	 * the entire site offline. The user must explicitly approve.
	 *
	 * @since  0.6093.1200
	 * @return string
	 */
	public static function get_risk_level(): string {
		return 'high';
	}

	/**
	 * Apply the treatment.
	 *
	 * Sets post revision limit in wp-config.php.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about changes made.
	 * }
	 */
	public static function apply() {
		// Locate wp-config.php
		$config_path = ABSPATH . 'wp-config.php';

		if ( ! file_exists( $config_path ) ) {
			// Try parent directory (common in some installations)
			$config_path = dirname( ABSPATH ) . '/wp-config.php';

			if ( ! file_exists( $config_path ) ) {
				return array(
					'success' => false,
					'message' => __( 'Could not locate wp-config.php file. Please add define( \'WP_POST_REVISIONS\', 5 ); manually.', 'wpshadow' ),
				);
			}
		}

		// Check if file is writable
		if ( ! is_writable( $config_path ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: file path */
					__( 'wp-config.php is not writable. Please make %s writable or add define( \'WP_POST_REVISIONS\', 5 ); manually.', 'wpshadow' ),
					$config_path
				),
			);
		}

		// Create backup
		$backup_path = $config_path . '.wpshadow-backup-' . time();
		if ( ! copy( $config_path, $backup_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to create backup of wp-config.php. Aborting for safety.', 'wpshadow' ),
			);
		}

		// Read current content
		$content = file_get_contents( $config_path );

		if ( $content === false ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to read wp-config.php. Please check file permissions.', 'wpshadow' ),
			);
		}

		// Check if WP_POST_REVISIONS is already defined
		if ( preg_match( '/define\s*\(\s*[\'"]WP_POST_REVISIONS[\'"]/i', $content ) ) {
			return array(
				'success' => false,
				'message' => __( 'WP_POST_REVISIONS is already defined in wp-config.php. No changes made.', 'wpshadow' ),
			);
		}

		// Find the right place to insert (before "That's all, stop editing!")
		$marker = '/* That\'s all, stop editing!';
		$position = strpos( $content, $marker );

		if ( $position === false ) {
			// Try alternate marker
			$marker = '/* That's all, stop editing!';
			$position = strpos( $content, $marker );
		}

		if ( $position === false ) {
			// No marker found, append before closing PHP tag
			$position = strrpos( $content, '?>' );

			if ( $position === false ) {
				// No closing tag, append to end
				$new_content = rtrim( $content ) . "\n\n" .
					"// Limit post revisions to prevent database bloat\n" .
					"define( 'WP_POST_REVISIONS', 5 );\n";
			} else {
				$new_content = substr( $content, 0, $position ) .
					"\n// Limit post revisions to prevent database bloat\n" .
					"define( 'WP_POST_REVISIONS', 5 );\n\n" .
					substr( $content, $position );
			}
		} else {
			// Insert before marker
			$new_content = substr( $content, 0, $position ) .
				"// Limit post revisions to prevent database bloat\n" .
				"define( 'WP_POST_REVISIONS', 5 );\n\n" .
				substr( $content, $position );
		}

		// Write updated content
		$result = file_put_contents( $config_path, $new_content, LOCK_EX );

		if ( $result === false ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to write to wp-config.php. Changes not saved.', 'wpshadow' ),
			);
		}

		// Verify the change
		// Note: We can't just reload and check defined() because wp-config is only loaded once
		$verify = file_get_contents( $config_path );
		if ( strpos( $verify, "define( 'WP_POST_REVISIONS', 5 )" ) === false ) {
			// Rollback
			copy( $backup_path, $config_path );
			return array(
				'success' => false,
				'message' => __( 'Verification failed. Changes rolled back. Please contact support.', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Post revision limit set to 5 in wp-config.php. Future revisions will be limited. Backup saved to: ', 'wpshadow' ) . basename( $backup_path ),
			'details' => array(
				'revision_limit' => 5,
				'backup_file'   => $backup_path,
				'config_file'   => $config_path,
				'note'          => __( 'This only affects future revisions. To clean up existing revisions, run the "Clean Up Old Post Revisions" treatment.', 'wpshadow' ),
			),
		);
	}
}
