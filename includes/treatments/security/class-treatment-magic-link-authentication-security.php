<?php
/**
 * Magic Link Authentication Security Treatment
 *
 * Detects and analyzes magic link authentication implementations for security issues.
 * Magic links (passwordless login) must verify tokens are cryptographically strong,
 * single-use, time-limited, and properly validated. Weak implementation = session theft.
 *
 * **What This Check Does:**
 * - Detects if magic link authentication implemented
 * - Validates token generation (cryptographically secure random)
 * - Confirms tokens are single-use (not replayable)
 * - Tests token expiration (usually 15-30 minutes)
 * - Checks if token transmitted over HTTPS only
 * - Validates email verification before accepting token
 *
 * **Why This Matters:**
 * Weak magic link implementation = account takeover via token interception. Scenarios:
 * - Magic token predictable (weak random)
 * - Attacker guesses token format, generates valid tokens
 * - Token not time-limited (reusable indefinitely)
 * - Token stored in plaintext in database
 * - Link intercepted in transit (email forwarded)
 *
 * **Business Impact:**
 * SaaS platform implements magic links for "passwordless" convenience. Token
 * generation uses weak random (based on timestamp only). Attacker predicts token
 * format. Intercepts user email (email forwarded to attacker). Attacker replays
 * token from different IP. Gains account access. Premium account credentials stolen.
 * Cost: $100K account takeover liability + recovery.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Passwordless login still secure
 * - #9 Show Value: Prevents token replay attacks
 * - #10 Beyond Pure: Security-first passwordless authentication
 *
 * **Related Checks:**
 * - Authentication Cookie Security (token validation)
 * - Email Verification Implementation (email safety)
 * - Session Management (session reuse prevention)
 *
 * **Learn More:**
 * Magic link security: https://wpshadow.com/kb/magic-link-authentication
 * Video: Secure passwordless login (12min): https://wpshadow.com/training/magic-links
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Magic Link Authentication Security Treatment Class
 *
 * Analyzes magic link implementations for security vulnerabilities.
 *
 * **Detection Pattern:**
 * 1. Check if magic link plugin/feature active
 * 2. Validate token generation method
 * 3. Test token entropy (cryptographic randomness)
 * 4. Confirm token single-use enforcement
 * 5. Check token expiration (time-limited)
 * 6. Return severity if weak implementation found
 *
 * **Real-World Scenario:**
 * Developer creates magic link feature. Uses md5(email + timestamp) as token.
 * Attacker knows user email + approximate time. Generates likely token.
 * Tries token. Matches. Gains account access. Weak random = security failure.
 * Production should use random_bytes() + strong hashing.
 *
 * **Implementation Notes:**
 * - Checks for magic link plugin
 * - Validates random generation (random_bytes required)
 * - Confirms single-use, time-limited tokens
 * - Severity: critical (predictable tokens), high (weak validation)
 * - Treatment: use secure token generation + expiration
 *
 * @since 1.6093.1200
 */
class Treatment_Magic_Link_Authentication_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'magic-link-authentication-security';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Magic Link Authentication Security';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects and analyzes magic link authentication implementations';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Magic_Link_Authentication_Security' );
	}
}
