<?php
/**
 * Plugin Authentication Bypass Risk Treatment
 *
 * Detects plugins vulnerable to authentication bypass attacks.
 * Vulnerable plugin = attacker skips login, directly accesses admin functions.
 * Bypass = account takeover without guessing password.
 *
 * **What This Check Does:**
 * - Scans active plugins for authentication bypass vulnerabilities
 * - Checks if plugins verify nonces on admin actions
 * - Tests if WordPress capabilities checked
 * - Detects if authentication functions bypassed
 * - Validates user_can() checks present
 * - Tests for known authentication bypass patterns
 *
 * **Why This Matters:**
 * Authentication bypass = complete site takeover. Scenarios:
 * - Plugin has admin function accessible without login
 * - Attacker calls function directly (via AJAX/REST)
 * - Bypasses authentication completely
 * - No password needed (authentication skipped)
 * - Attacker gains full admin access
 *
 * **Business Impact:**
 * Site uses popular plugin with authentication bypass (unpatched vulnerability).
 * Attacker sends crafted URL. Plugin admin function executes without login.
 * Attacker creates admin account. Takes over site. Installs malware. Breach
 * damages: $500K+. Plugin developer fixes (1 month delay). Site compromised
 * entire time. Early detection via vulnerability scanning prevents entirely.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Plugins validated for auth security
 * - #9 Show Value: Prevents bypass-based compromise
 * - #10 Beyond Pure: Trust but verify (dependencies)
 *
 * **Related Checks:**
 * - Plugin Vulnerability Detection (general plugin security)
 * - CSRF Protection (related attack vector)
 * - Nonce Validation (authentication pattern)
 *
 * **Learn More:**
 * Plugin security: https://wpshadow.com/kb/wordpress-plugin-vulnerabilities
 * Video: Securing WordPress plugins (13min): https://wpshadow.com/training/plugin-security
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
 * Treatment_Plugin_Authentication_Bypass_Risk Class
 *
 * Identifies plugins vulnerable to authentication bypass.
 *
 * **Detection Pattern:**
 * 1. Scan active plugin files
 * 2. Check for AJAX handlers without nonce verification
 * 3. Detect admin functions callable without capability check
 * 4. Test for unauthenticated REST endpoints
 * 5. Validate proper authentication flow
 * 6. Return severity if bypass vulnerability found
 *
 * **Real-World Scenario:**
 * Plugin dev creates AJAX handler: forgot to check current_user_can().
 * Handler accessible to unauthenticated users. Attacker discovers via proxy
 * inspection. Crafts request. Executes admin function as unauthenticated.
 * Creates admin account. Takes over site. All without knowing admin password.
 * Proper implementation: check_ajax_referer() + current_user_can() required.
 *
 * **Implementation Notes:**
 * - Scans active plugin files for auth patterns
 * - Checks for missing nonce/capability verification
 * - Tests AJAX handlers + REST endpoints
 * - Severity: critical (bypass found), high (weak checks)
 * - Treatment: update plugin or replace with secure alternative
 *
 * @since 0.6093.1200
 */
class Treatment_Plugin_Authentication_Bypass_Risk extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-authentication-bypass-risk';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Authentication Bypass Risk';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins with weak authentication handling';

	/**
	 * Treatment family
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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Authentication_Bypass_Risk' );
	}
}
