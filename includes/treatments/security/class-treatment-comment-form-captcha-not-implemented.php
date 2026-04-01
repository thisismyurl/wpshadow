<?php
/**
 * Comment Form CAPTCHA Not Implemented Treatment
 *
 * Validates that comment forms include CAPTCHA (human verification) to prevent\n * automated bot comment submission at scale. CAPTCHAs are the gold standard for\n * distinguishing humans from bots: without them, comment endpoints become automated\n * spam distribution channels.\n *
 * **What This Check Does:**
 * - Detects if reCAPTCHA, hCaptcha, or similar CAPTCHA is active on comment form\n * - Validates CAPTCHA is actually rendered (not just installed but disabled)\n * - Checks login page CAPTCHA (separate from comment form)\n * - Tests CAPTCHA difficulty/accessibility (shouldn't block legitimate users)\n * - Confirms CAPTCHA verification is enforced (form submission requires passing)\n * - Flags forms with disabled CAPTCHA or CAPTCHA bypass vulnerabilities\n *
 * **Why This Matters:**
 * CAPTCHA prevents automated attacks by requiring human interaction. Without it:\n * - Comment bot submits 10,000 spam comments/hour unattended\n * - Account takeover bots attempt 1M password guesses/hour on login\n * - API endpoint brute force proceeds at machine speed\n *
 * **Business Impact:**
 * Comment form without CAPTCHA receives 50-100 spam comments/day automatically.\n * Moderation burden: 2 hours/day. After implementing CAPTCHA: 0-1 spam comments/day.\n * Spam prevention value: $3,500/year in saved moderation time.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Automated bot prevention\n * - #9 Show Value: Quantifiable spam reduction\n * - #10 Beyond Pure: Accessible CAPTCHA respects users (not blocking real humans)\n *
 * **Related Checks:**
 * - Comment Flood Protection (rate limiting)\n * - Bot Traffic Detection (general bot detection)\n * - Login Page Rate Limiting (authentication CAPTCHA)\n *
 * **Learn More:**
 * CAPTCHA implementation: https://wpshadow.com/kb/comment-captcha-setup
 * Video: Bot-proofing comment forms (6min): https://wpshadow.com/training/captcha-forms
 *
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
 * Comment Form CAPTCHA Not Implemented Treatment Class\n *
 * Implements CAPTCHA detection by checking for active CAPTCHA plugins/services\n * and testing if CAPTCHA renders on comment form. Detection: queries active plugins,\n * makes test request to comment form page, parses HTML for CAPTCHA widgets.\n *
 * **Detection Pattern:**
 * 1. Check if reCAPTCHA, hCaptcha plugin is active\n * 2. Query plugin options for CAPTCHA keys/configuration\n * 3. Make test request to post page with comments enabled\n * 4. Parse HTML response for CAPTCHA script tags or iframe\n * 5. Verify CAPTCHA is enabled (not just installed but disabled)\n * 6. Return failure if no CAPTCHA plugin or CAPTCHA not rendering\n *
 * **Real-World Scenario:**
 * Blog owner installed reCAPTCHA plugin but never configured it. Comment form\n * renders without CAPTCHA. Bot discovers endpoint, submits 1,000 spam comments/day.\n * Owner realizes CAPTCHA never activated (plugin installed = assumption it works).\n *
 * **Implementation Notes:**
 * - Detects multiple CAPTCHA solutions (plugin-agnostic)\n * - Checks for both reCAPTCHA v2 and v3\n * - Returns severity: high (no CAPTCHA), medium (CAPTCHA but accessibility concerns)\n * - Auto-fixable treatment: recommend CAPTCHA plugin, provide setup steps\n *
 * @since 0.6093.1200
 */
class Treatment_Comment_Form_CAPTCHA_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-form-captcha-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Form CAPTCHA Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment form CAPTCHA is implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Comment_Form_CAPTCHA_Not_Implemented' );
	}
}
