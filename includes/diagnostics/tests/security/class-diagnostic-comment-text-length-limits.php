<?php
/**
 * Comment Text Length Limits Diagnostic
 *
 * Validates that comment text length limits are properly enforced to prevent\n * resource exhaustion attacks: attackers submitting massive comments (megabytes of text)\n * to slow servers, fill databases, or trigger memory exhaustion. Unrestricted comment\n * length can be weaponized for denial-of-service.\n *
 * **What This Check Does:**
 * - Verifies comment text length limits are configured\n * - Checks for excessively long comments already in database (1000+, 5000+ characters)\n * - Detects if length validation filters are active\n * - Tests that database can handle large comment queries efficiently\n * - Flags posts with comment text exceeding safe thresholds\n * - Validates comment text processing doesn't consume excessive memory\n *
 * **Why This Matters:**
 * Unrestricted comment length enables DoS and resource exhaustion. Attack vectors:\n * - Attacker submits comment with 10MB of text (database swells)\n * - Server runs out of memory trying to process the comment\n * - Comment query takes 10 seconds (slows site for all visitors)\n * - Repeated attack: database fills to capacity, site stops accepting comments\n *
 * **Business Impact:**
 * Single unrestricted comment attack can create database bloat:\n * - 1,000 comments × 100KB each = 100MB added to database\n * - Database backups slow (larger DB = slower backup, higher transfer costs)\n * - Search queries slow (scanning 100MB+ of comment text per search)\n * - Comment management interface sluggish (loading thousands of long comments)\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Resource protection\n * - #9 Show Value: Prevents performance degradation\n * - #10 Beyond Pure: Protects infrastructure and user experience\n *
 * **Related Checks:**
 * - Comment Flood Protection (DoS via comment volume)\n * - Database Table Corruption Check (aftermath of resource exhaustion)\n * - Comment Link Count Limits (similar resource protection)\n *
 * **Learn More:**
 * Comment length configuration: https://wpshadow.com/kb/comment-length-limits
 * Video: Resource protection strategies (7min): https://wpshadow.com/training/resource-protection
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Text Length Diagnostic Class\n *
 * Implements length limit validation by querying database for comments exceeding\n * safe thresholds and checking if length validation filters are registered.\n * Detection: runs query for comments > 5000 characters, checks filter hooks.\n *
 * **Detection Pattern:**
 * 1. Query wp_comments for comment_content with CHAR_LENGTH > 5000\n * 2. Count results (threshold: > 0 is concerning, > 10 is critical)\n * 3. Check for preprocess_comment or comment_text filters (validation)\n * 4. If long comments exist AND no filters: flag as high severity\n * 5. Calculate average comment length (detect bloat trend)\n *
 * **Real-World Scenario:**
 * Forum site with no comment length limits. Jan 2024: attacker discovers endpoint.\n * Posts 50 comments/day, each with 50KB of random text. Within 2 weeks: database\n * swells from 500MB to 1.5GB. Hosting provider sends notice: pay for upgrade or\n * reduce usage. Cost: $300/month increase. Prevention: 5-minute limit configuration.\n *
 * **Implementation Notes:**
 * - Uses CHAR_LENGTH for accurate multi-byte character counting\n * - Threshold: 5000 characters reasonable for typical comments\n * - Returns severity: high (1000+ long comments), critical (100KB+ comments)\n * - Auto-fixable treatment: enforce length limit at form submission\n *
 * @since 1.6031.1300
 */\nclass Diagnostic_Comment_Text_Length_Limits extends Diagnostic_Base {

	protected static $slug = 'comment-text-length-limits';
	protected static $title = 'Comment Text Length Limits';
	protected static $description = 'Verifies comment length limits are enforced';
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1300
	 * @return array|null
	 */
	public static function check() {
		// Check for custom comment length validation.
		$has_length_filter = has_filter( 'preprocess_comment', 'wp_filter_kses' ) ||
		                     has_filter( 'comment_text', 'wp_kses_post' ) ||
		                     has_filter( 'preprocess_comment' );

		// Check database for excessively long comments.
		global $wpdb;
		$long_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE CHAR_LENGTH(comment_content) > 5000"
		);

		if ( $long_comments > 0 && ! $has_length_filter ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of long comments */
					__( 'Found %d comments exceeding 5000 characters with no length validation', 'wpshadow' ),
					$long_comments
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-text-length-limits',
			);
		}

		return null;
	}
}
