<?php
/**
 * User Session Security Treatment
 *
 * Validates that user sessions are properly secured with appropriate
 * timeouts, cookie flags, and security headers.
 * Insecure sessions = attacker steals cookie. Hijacks session.
 * Secure sessions = HttpOnly, Secure, SameSite flags. Hijacking harder.
 *
 * **What This Check Does:**
 * - Checks session cookie flags (HttpOnly, Secure, SameSite)
 * - Validates session timeout configured
 * - Tests session regeneration on privilege change
 * - Checks if concurrent sessions limited
 * - Validates session token rotation
 * - Returns severity if session insecure
 *
 * **Why This Matters:**
 * Session cookie without HttpOnly flag = JavaScript can read.
 * XSS attack steals cookie. Attacker hijacks session.
 * With HttpOnly: JavaScript blocked. Cookie theft harder.
 *
 * **Business Impact:**
 * Admin session cookie lacks HttpOnly flag. XSS vulnerability exists.
 * Attacker injects script. Steals admin cookie. Hijacks session.
 * Full admin access. Site compromised. Cost: $500K+. With HttpOnly:
 * script can't read cookie. Session hijacking blocked. Admin safe.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Sessions hardened against theft
 * - #9 Show Value: Prevents session hijacking
 * - #10 Beyond Pure: Defense-in-depth session security
 *
 * **Related Checks:**
 * - Cookie Security Configuration (related)
 * - XSS Protection (complementary)
 * - HTTPS Enforcement (required for Secure flag)
 *
 * **Learn More:**
 * Session security: https://wpshadow.com/kb/session-security
 * Video: Securing sessions (11min): https://wpshadow.com/training/sessions
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
 * User Session Security Treatment Class
 *
 * Checks user session security configuration.
 *
 * **Detection Pattern:**
 * 1. Inspect session cookie attributes
 * 2. Check HttpOnly, Secure, SameSite flags
 * 3. Validate session timeout settings
 * 4. Test session regeneration on login
 * 5. Check concurrent session limits
 * 6. Return each missing security measure
 *
 * **Real-World Scenario:**
 * Session cookies have Secure and HttpOnly flags. Attacker finds XSS.
 * Injects script to steal cookies. Script runs but can't read cookies
 * (HttpOnly blocks). Attacker fails. With insecure cookies: script
 * reads cookie value. Sends to attacker. Session hijacked.
 *
 * **Implementation Notes:**
 * - Checks session cookie configuration
 * - Validates security flags
 * - Tests timeout and regeneration
 * - Severity: high (missing HttpOnly/Secure)
 * - Treatment: configure session cookies with security flags
 *
 * @since 0.6093.1200
 */
class Treatment_User_Session_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-session-security';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'User Session Security';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user session security configuration';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_User_Session_Security' );
	}
}
