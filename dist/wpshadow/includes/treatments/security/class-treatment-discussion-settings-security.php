<?php
<?php
/**
 * Discussion Settings Security Treatment
 *
 * Validates comment/discussion security settings to prevent spam, abuse, and\n * unauthorized comment publication. Misconfigured discussion settings allow spam\n * bots to flood sites with malicious comments containing phishing links.\n *
 * **What This Check Does:**
 * - Checks if comment moderation enabled (before_publish option)\n * - Validates comment author email/name required\n * - Detects if auto-close comments after X days enabled\n * - Tests if new comment notifications sent to admin\n * - Checks if comment author must have previous approved comment (whitelist)\n * - Validates CAPTCHA or similar SPAM protection\n *
 * **Why This Matters:**
 * Permissive discussion settings flood site with spam/malware. Scenarios:\n * - Comments auto-approve without moderation\n * - Spam bots submit 10,000 comments/day containing malware links\n * - Comments published immediately to site (and search engines)\n * - Legitimate users click malware links, get infected\n * - Site reputation damaged (appears to endorse malware)\n *
 * **Business Impact:**
 * Blog with open comments enabled, auto-approve. Spam bot discovers via WPScan.\n * Submits 5,000 comments/day with malware URLs. Comments appear on blog posts.\n * Google crawls comments, indexes malware links. Blog appears in search \"malware\"\n * queries. Site blacklisted by Google Safe Browsing. Traffic drops 90%.\n * Recovery: 1 week blacklist delisting process.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Comments safe and relevant\n * - #9 Show Value: Prevents spam/malware distribution\n * - #10 Beyond Pure: Respects user safety (not serving malware)\n *
 * **Related Checks:**
 * - Comment Form CAPTCHA Not Implemented (human verification)\n * - Comment Flood Protection (rate limiting)\n * - Bot Traffic Detection Not Implemented (general bot prevention)\n *
 * **Learn More:**
 * Discussion security setup: https://wpshadow.com/kb/wordpress-discussion-security\n * Video: Configuring comment moderation (7min): https://wpshadow.com/training/comment-security\n *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Discussion Settings Security Treatment Class
 *
 * Implements validation of comment/discussion security options.\n *
 * **Detection Pattern:**
 * 1. Get discussion options: comment_moderation, moderation_notify, etc.\n * 2. Check if comments before_publish vs after_publish\n * 3. Validate comment_registration, require_name_email\n * 4. Check close_comments_for_old_posts\n * 5. Validate allow_comments_feed\n * 6. Return severity if insecure settings found\n *
 * **Real-World Scenario:**
 * Default WordPress discussion settings. Comments allowed, no moderation,\n * doesn't require email. Spam bot auto-discovers via WPScan. Posts 10 comments/min.\n * 500 comments/hour with casino/pharma links. Moderator drowning in notifications.\n * Admin manually deletes comments for 3 hours, gives up. Site full of spam.\n *
 * **Implementation Notes:**
 * - Uses get_option() for all discussion settings\n * - Checks: comment_moderation, require_name_email, close_comments_for_old_posts\n * - Validates notification settings\n * - Severity: high (no moderation), medium (weak moderation)\n * - Treatment: enable moderation, require email, disable old comments\n *
 * @since 0.6093.1200
 */
class Treatment_Discussion_Settings_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'discussion-settings-security';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Discussion Settings Security';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment/discussion security settings';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Security';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Discussion_Settings_Security' );
	}
}
