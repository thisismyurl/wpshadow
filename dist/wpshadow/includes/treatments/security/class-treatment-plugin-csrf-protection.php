<?php
/**
 * Plugin CSRF Protection Treatment
 *
 * Detects plugins missing CSRF (Cross-Site Request Forgery) nonce protection.
 * CSRF = attacker tricks admin into performing unwanted action (via email/website).
 * Plugin without nonce = attacker can execute admin functions from external site.
 *
 * **What This Check Does:**
 * - Scans plugin files for form submissions + AJAX handlers
 * - Checks if nonces verified on all requests
 * - Detects missing wp_verify_nonce() calls
 * - Tests if state parameter validated
 * - Validates referer checks present
 * - Returns severity if CSRF unprotected
 *
 * **Why This Matters:**
 * CSRF allows action impersonation. Scenarios:
 * - Plugin admin page accepts form without nonce
 * - Attacker crafts malicious email
 * - Admin clicks link in email (attacker's website)
 * - Attacker's page makes request to admin page
 * - Admin's browser: "Oh, admin wants to delete all users"
 * - Request executes (no nonce verification)
 * - All users deleted
 *
 * **Business Impact:**
 * E-commerce site uses plugin to manage customers. Plugin CSRF unprotected.
 * Competitor sends admin crafted email: "Check out this deal". Admin clicks.
 * Email contains hidden form. Submits to plugin admin page. Deletes all
 * customer data (via CSRF). Site loses database. Recovery: $500K+ cost.
 * Nonce verification: 10 minutes to add. Prevents $500K loss entirely.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Admin actions protected from impersonation
 * - #9 Show Value: Prevents admin action abuse
 * - #10 Beyond Pure: Defense against social engineering
 *
 * **Related Checks:**
 * - Authentication Cookie Security (session protection)
 * - Plugin Authentication Bypass (similar surface)
 * - Form Validation (input integrity)
 *
 * **Learn More:**
 * CSRF protection: https://wpshadow.com/kb/wordpress-csrf-protection
 * Video: Understanding CSRF attacks (10min): https://wpshadow.com/training/csrf-basics
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
 * Treatment_Plugin_CSRF_Protection Class
 *
 * Identifies plugins missing CSRF nonce protection.
 *
 * **Detection Pattern:**
 * 1. Scan plugin files for form submissions
 * 2. Check for wp_nonce_field() in forms
 * 3. Detect missing wp_verify_nonce() in handlers
 * 4. Test AJAX requests for nonce validation
 * 5. Validate state parameter in OAuth flows
 * 6. Return severity if CSRF unprotected
 *
 * **Real-World Scenario:**
 * Plugin handles user deletion. Form in admin page doesn't include nonce.
 * Admin receives email: "Update security settings". Email has hidden form.
 * Form submits to plugin deletion page (no nonce check needed). Admin's
 * browser silently performs request. Deletes users. With nonce: email's
 * request fails (nonce invalid). Site protected.
 *
 * **Implementation Notes:**
 * - Scans plugin files for unprotected forms/AJAX
 * - Checks for nonce fields + verification
 * - Tests actual form submission
 * - Severity: critical (no CSRF protection), medium (partial protection)
 * - Treatment: add wp_nonce_field() + wp_verify_nonce() to all forms/AJAX
 *
 * @since 1.6093.1200
 */
class Treatment_Plugin_CSRF_Protection extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-csrf-protection';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin CSRF Protection';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins missing CSRF nonce verification';

	/**
	 * Treatment family
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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_CSRF_Protection' );
	}
}
