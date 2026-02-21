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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Whitelist_Bypass' );
	}
}
