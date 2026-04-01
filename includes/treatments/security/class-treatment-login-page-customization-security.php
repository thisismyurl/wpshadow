<?php
<?php
/**
 * Login Page Customization Security Treatment
 *
 * Checks security implications of login page customization. Custom login pages\n * may accidentally expose information, disable security features, or introduce\n * vulnerabilities (reflected XSS, CSRF). Customizations must maintain security.\n *
 * **What This Check Does:**
 * - Detects if login page customized (non-default template)\n * - Validates nonce tokens present (CSRF protection)\n * - Checks for information disclosure (errors reveal usernames)\n * - Tests if redirect after login is controlled (open redirect prevention)\n * - Confirms SSL/HTTPS enforced on login form\n * - Validates password field is type=\"password\" (not visible in browser)\n *
 * **Why This Matters:**
 * Custom login = accidentally broken security. Scenarios:\n * - Developer removes nonce (forgot to add to custom form)\n * - CSRF attack possible (attacker tricks user into login)\n * - Custom error message leaks user existence (\"User not found\")\n * - Attacker enumerates valid usernames\n * - Custom redirect doesn't validate origin (open redirect to malware)\n *
 * **Business Impact:**
 * Freelancer creates custom login form. Forgets nonce field (didn't know about\n * CSRF). Site vulnerable to CSRF. Attacker tricks admin into submitting form that\n * creates new admin account (attacker controlled). Attacker gains permanent access.\n * Discovers breach 3 months later. Damage: $100K+ in breach investigation + recovery.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Custom features still secure\n * - #9 Show Value: Prevents CSRF/redirect vulnerabilities\n * - #10 Beyond Pure: Security by default, even with customization\n *
 * **Related Checks:**
 * - Cross-Site Request Forgery Protection Not Validated (CSRF)\n * - Input Sanitization Not Implemented (XSS prevention)\n * - Login Page Rate Limiting (brute force)\n *
 * **Learn More:**
 * Custom login form security: https://wpshadow.com/kb/custom-login-form-security\n * Video: Building secure login forms (11min): https://wpshadow.com/training/custom-login-security\n *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Page Customization Security Treatment Class
 *
 * Validates custom login page for security issues.\n *
 * **Detection Pattern:**
 * 1. Detect if custom login template used (not /wp-login.php)\n * 2. Scan form HTML for nonce fields\n * 3. Check for CSRF protection\n * 4. Validate redirect_to parameter sanitization\n * 5. Check if SSL/HTTPS enforced\n * 6. Return severity if security features missing\n *
 * **Real-World Scenario:**
 * Developer creates custom login template. Copies from tutorial. Tutorial didn't\n * include nonce field (written before WordPress nonce best practices). Custom form\n * works fine. Attacker crafts CSRF payload. Tricks admin into visiting. Form\n * silently submitted to create new admin user. Attacker now has permanent access.\n *
 * **Implementation Notes:**
 * - Checks for custom login template usage\n * - Scans form for nonce field\n * - Validates redirect parameter\n * - Severity: high (missing nonce), medium (weak validation)\n * - Treatment: add CSRF tokens, validate redirects\n *
 * @since 0.6093.1200
 */
class Treatment_Login_Page_Customization_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-page-customization-security';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Login Page Customization Security';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks security implications of login page customization';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Login_Page_Customization_Security' );
	}
}
