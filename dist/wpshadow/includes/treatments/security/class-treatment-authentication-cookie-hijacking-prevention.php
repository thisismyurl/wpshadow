<?php
/**
 * Authentication Cookie Hijacking Prevention Treatment
 *
 * Validates security measures protecting WordPress authentication cookies from theft\n * and replay attacks. Authentication cookies are golden tickets: steal one, gain account\n * access without knowing password. Common theft vectors: network sniffing (HTTP), malicious\n * JavaScript (XSS), malware on client machine.\n *
 * **What This Check Does:**
 * - Verifies HTTPS-only cookie flag is set (prevents network sniffing)\n * - Confirms HttpOnly cookie flag prevents JavaScript access (stops XSS theft)\n * - Checks for SameSite cookie attribute (prevents CSRF/cross-site cookie usage)\n * - Validates secure cookie settings are persistent and enforced\n * - Tests that authentication cookies expire after reasonable timeout\n * - Detects if cookies are still sent over HTTP (unencrypted compromise)\n *
 * **Why This Matters:**
 * Authentication cookie theft = account hijacking without password reset capability. Scenarios:\n * - Network MITM on public WiFi intercepts unencrypted cookie, replays it\n * - Malicious ad injects XSS script, steals cookie from browser\n * - Malware on computer extracts cookie from browser cache\n * - CSRF attack tricks browser into using cookie on malicious site\n *
 * **Business Impact:**
 * Cookie hijacking usually undetectable: attacker uses your account, leaving no login trace.\n * Scenario: Public WiFi attacker steals admin cookie. Modifies site to inject malware.\n * Malware on-clicked by 1,000 visitors. Within 24 hours: 50 new infections, ISP complaint,\n * site listed in malware databases. Recovery: 40 hours cleanup, reputation damage, lost revenue.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Multi-layered authentication protection\n * - #9 Show Value: Eliminates entire hijacking attack class\n * - #10 Beyond Pure: Protects users even if they use public WiFi\n *
 * **Related Checks:**
 * - HTTPS Enforcement (transport security)\n * - User Capability Auditing (detect if hijacked account used)\n * - Login Page Rate Limiting (detect brute force attempts)\n *
 * **Learn More:**
 * Cookie security hardening: https://wpshadow.com/kb/auth-cookie-protection
 * Video: WordPress security best practices (15min): https://wpshadow.com/training/auth-security
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
 * Authentication Cookie Hijacking Prevention Treatment Class
 *
 * Implements cookie security validation by reading WordPress configuration constants\n * and making test requests to verify cookie headers. Detection: checks\n * COOKIEHTTPONLY, COOKIESECURE, FORCE_SSL_ADMIN constants, examines Set-Cookie response\n * headers for security flags.\n *
 * **Detection Pattern:**
 * 1. Check define( 'COOKIEHTTPONLY', true ) in wp-config.php\n * 2. Check define( 'COOKIESECURE', true ) if HTTPS active\n * 3. Check define( 'FORCE_SSL_ADMIN', true )\n * 4. Make test request to login page, inspect Set-Cookie response headers\n * 5. Verify headers include: Secure, HttpOnly, SameSite=Lax/Strict\n * 6. Return failure if any security flag missing\n *
 * **Real-World Scenario:**
 * Business site behind corporate proxy with SSL inspection. Developer left COOKIEHTTPONLY\n * = false for \"debugging.\" Attacker on same corporate network uses packet sniffer, captures\n * authentication cookie. Attacker injects malware via admin panel. By the time company\n * detected it, malware infected 10,000 client machines. Impact: $500K+ liability, contract\n * terminations, criminal investigation.\n *
 * **Implementation Notes:**
 * - Reads wp-config.php constants or uses get_option fallbacks\n * - Makes real HTTP test to verify header presence\n * - Returns severity: critical (no security flags), high (partial protection)\n * - Non-fixable treatment (requires wp-config.php modification)\n *
 * @since 0.6093.1200
 */
class Treatment_Authentication_Cookie_Hijacking_Prevention extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'authentication-cookie-hijacking-prevention';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Authentication Cookie Hijacking Prevention';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for protections against authentication cookie hijacking';

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
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Authentication_Cookie_Hijacking_Prevention' );
	}
}
