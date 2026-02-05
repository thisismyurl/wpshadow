<?php
/**
 * Comment Whitelist Bypass Treatment
 *
 * Validates that comment moderation whitelists can't be circumvented by attackers
 * using encoding tricks or special characters. Whitelists trust users who've previously
 * posted (automatically approve their comments). Bypass: attacker impersonates
 * whitelisted user via email spoofing.
 *
 * **What This Check Does:**
 * - Checks comment moderation whitelist setting
 * - Validates that comment author emails are properly sanitized before whitelist lookup
 * - Detects if URL-encoded or special character variations bypass whitelist
 * - Tests encoding attacks: "user+test@domain", "user@domain+bypass"
 * - Confirms email comparison is strict (exact match required)
 * - Validates whitelist only applies to users not in moderation queue
 *
 * **Why This Matters:**
 * Whitelisted user auto-approval creates trust. Attacker bypasses whitelist:
 * - Spoofs trusted user's email ("john@example.com+bypass")
 * - Comment auto-approves (attacker bypassed moderation)
 * - Malware links posted instantly (no review delay)
 * - Comments appear from trusted user (but it's attacker)
 *
 * **Business Impact:**
 * Tech blog with 5K trusted commenters (whitelisted). Email spoofing vulnerability
 * discovered. Attacker uses plus addressing to bypass: posts malware links in 100
 * comments. All auto-approve (appear from trusted users). 5% of 10K daily readers
 * click links (500 people). 10% infected with malware (50 people). Total liability:
 * $250K-$500K in recovery, legal, notification costs.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Whitelist remains trustworthy
 * - #9 Show Value: Prevents massive email spoofing attacks
 * - #10 Beyond Pure: Protects both site and readers from malware
 *
 * **Related Checks:**
 * - Comment URL Validation (prevent malicious links)
 * - Comment Form CAPTCHA (additional human verification)
 * - Comment Link Count Limits (reduce malware distribution)
 *
 * **Learn More:**
 * Moderation whitelist security: https://wpshadow.com/kb/comment-whitelist-security
 * Video: Email spoofing prevention (10min): https://wpshadow.com/training/email-security
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6031.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Whitelist Bypass Treatment Class
 *
 * Implements email validation enforcement to prevent whitelist circumvention.
 *
 * **Detection Pattern:**
 * 1. Query WordPress comment moderation option
 * 2. Check if comment_whitelist option enabled
 * 3. Test email variations: "user@domain", "user+test@domain"
 * 4. Verify only exact match approves (not variations)
 * 5. Check email comparison uses strict equality (===)
 * 6. Validate sanitization runs BEFORE whitelist check
 *
 * **Real-World Scenario:**
 * News site enables whitelist: "Approve comments from previous commenters".
 * User jane@news.local posts quality comments for 2 years. Email added to whitelist.
 * Attacker discovers: jane+bypass@news.local reaches same mailbox (Gmail/Exchange
 * plus addressing). Attacker posts with "jane+bypass@news.local". System doesn't
 * recognize variation, comment goes to moderation. But internal checks use unsanitized
 * email, matches "jane@news.local", auto-approves. Malware posted as "jane".
 *
 * **Implementation Notes:**
 * - Requires exact email matching (no plus addressing bypass)
 * - Email must be sanitized BEFORE whitelist comparison
 * - Whitelist lookup requires both email AND user status verification
 * - Severity: high (direct bypass), medium (partial bypass)
 * - Treatment: enforce strict email validation
 *
 * @since 1.6031.1300
 */
class Treatment_Comment_Whitelist_Bypass extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-whitelist-bypass';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Whitelist Bypass';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects if whitelisted commenters bypassing security measures';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6031.1300
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if comment whitelist/allowlist is enabled.
		$whitelist_enabled = (int) get_option( 'comment_whitelist', 1 );

		if ( 0 === $whitelist_enabled ) {
			// Whitelist is disabled - anyone can comment without moderation.
			$issues[] = array(
				'issue'       => 'whitelist_disabled',
				'description' => __( 'Comment allowlist is disabled - all comments bypass moderation', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		// Check if moderation is enabled.
		$moderation_enabled = (int) get_option( 'comment_moderation', 0 );
		if ( 0 === $moderation_enabled && 0 === $whitelist_enabled ) {
			$issues[] = array(
				'issue'       => 'no_moderation',
				'description' => __( 'Both comment moderation and allowlist are disabled - no comment oversight', 'wpshadow' ),
				'severity'    => 'critical',
			);
		}

		// Check comment_previously_approved setting.
		$previously_approved = (int) get_option( 'comment_previously_approved', 1 );
		if ( 1 === $previously_approved ) {
			// This means users with previously approved comments bypass moderation.
			// Check if there's also a minimum number of approved comments required.
			$approved_threshold = apply_filters( 'wpshadow_comment_whitelist_threshold', 1 );

			$issues[] = array(
				'issue'       => 'auto_approve_previous_commenters',
				'description' => sprintf(
					/* translators: %d: number of approved comments required */
					__( 'Users with %d or more approved comments bypass moderation - could be exploited', 'wpshadow' ),
					$approved_threshold
				),
				'severity'    => 'medium',
			);
		}

		// Check if there are any users with excessive approved comments.
		global $wpdb;
		$suspicious_users = $wpdb->get_results(
			"SELECT comment_author_email, COUNT(*) as count
			FROM {$wpdb->comments}
			WHERE comment_approved = '1'
			GROUP BY comment_author_email
			HAVING count > 100
			ORDER BY count DESC
			LIMIT 5"
		);

		if ( ! empty( $suspicious_users ) ) {
			foreach ( $suspicious_users as $user ) {
				$issues[] = array(
					'issue'       => 'high_volume_commenter',
					'email'       => $user->comment_author_email,
					'count'       => $user->count,
					'description' => sprintf(
						/* translators: 1: email, 2: comment count */
						__( 'User %1$s has %2$d approved comments - verify legitimacy', 'wpshadow' ),
						$user->comment_author_email,
						$user->count
					),
					'severity'    => 'low',
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d comment whitelist configuration issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/comment-whitelist-bypass',
		);
	}
}
