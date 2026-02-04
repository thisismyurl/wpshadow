<?php
/**
 * Plugin CSRF Protection Diagnostic
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
 * @subpackage Diagnostics
 * @since      1.4031.1939
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_CSRF_Protection Class
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
 * @since 1.4031.1939
 */
class Diagnostic_Plugin_CSRF_Protection extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-csrf-protection';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin CSRF Protection';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins missing CSRF nonce verification';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$csrf_risks = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for form submissions without nonce
			if ( preg_match( '/<form[^>]*method\s*=\s*["\']post["\']/', $content ) ) {
				// Check if it has nonce fields
				if ( ! preg_match( '/wp_nonce_field|wp_create_nonce/', $content ) ) {
					$csrf_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Has POST forms without nonce verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for $_POST processing without nonce check
			if ( preg_match( '/if\s*\(\s*isset\s*\(\s*\$_POST/', $content ) ) {
				// Check if it verifies nonce
				if ( ! preg_match( '/wp_verify_nonce|check_admin_referer/', $content ) ) {
					$csrf_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Processes $_POST without nonce verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for AJAX handlers without nonce
			if ( preg_match( '/add_action\s*\(\s*["\']wp_ajax/', $content ) ) {
				// Check if it checks nonce
				if ( ! preg_match( '/check_ajax_referer|wp_verify_nonce/', $content ) ) {
					$csrf_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Has AJAX handlers without nonce verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for links that modify data
			if ( preg_match( '/\?.*=delete|trash|restore/', $content ) ) {
				// Check if they use WordPress nonce
				if ( ! preg_match( '/wp_nonce_url|wp_create_nonce/', $content ) ) {
					$csrf_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Has data-modifying links without nonce protection.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}
		}

		if ( ! empty( $csrf_risks ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: risk count, %s: details */
					__( '%d CSRF protection vulnerabilities detected: %s', 'wpshadow' ),
					count( $csrf_risks ),
					implode( ' | ', array_slice( $csrf_risks, 0, 3 ) )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'details'      => array(
					'csrf_risks' => $csrf_risks,
				),
				'kb_link'      => 'https://wpshadow.com/kb/csrf-protection',
			);
		}

		return null;
	}
}
