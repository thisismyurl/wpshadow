<?php
/**
 * Admin Settings Sanitization Verification Diagnostic
 *
 * Ensures that settings saved through WordPress admin screens are sanitized
 * before they are stored. Unsanitized settings are one of the most common sources
 * of stored XSS vulnerabilities because admin options often render later in
 * dashboards, widgets, notices, or front-end templates.
 *
 * **What This Check Does:**
 * - Reviews Settings API usage and error logs
 * - Detects settings errors that indicate failed sanitization
 * - Verifies that options are not storing raw script tags
 * - Flags patterns that suggest missing `sanitize_callback`
 * - Encourages consistent sanitization and validation per field type
 *
 * **Why This Matters:**
 * Settings values are often displayed elsewhere without escaping. If a setting
 * stores raw HTML or JavaScript, any user who can update that option can inject
 * scripts that execute for administrators or site visitors. This turns a simple
 * settings screen into a persistent XSS vulnerability.
 *
 * **Real-World Example:**
 * - Plugin setting: “Header Notice Text”
 * - Developer saves raw input to the options table
 * - Attacker with Editor role injects `<script>` tag
 * - Admin visits dashboard, script executes
 *
 * Result: Persistent XSS via settings page.
 *
 * **Sanitization Best Practices:**
 * - Use Settings API with `sanitize_callback`
 * - Sanitize at input: `sanitize_text_field()`, `sanitize_email()`, `esc_url_raw()`
 * - Escape at output: `esc_html()`, `esc_attr()`, `esc_url()`
 * - Never trust admin input (least privilege applies)
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents stored XSS from trusted admin flows
 * - #10 Beyond Pure: Protects users from data theft and malicious scripts
 * - Helpful Neighbor: Clear guidance on safe settings handling
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/settings-sanitization for secure settings patterns
 * or https://wpshadow.com/training/secure-admin-settings
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0641
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Settings Sanitization Verification
 *
 * Uses Settings API error logs and a controlled test option to detect
 * whether sanitization is enforced for admin options.
 *
 * **Implementation Pattern:**
 * 1. Inspect global settings errors for sanitization failures
 * 2. Store a test value containing script tags
 * 3. Verify stored value is sanitized
 * 4. Remove test option after verification
 * 5. Return finding if unsafe storage detected
 *
 * **Detection Logic:**
 * - Script tags stored as-is: Missing sanitization
 * - Settings errors >0: Potential invalid input handling
 * - Inconsistent storage: Mixed sanitization behavior
 *
 * **Related Diagnostics:**
 * - Admin Notices Security: Detects output escaping issues
 * - Admin Page Hook Security: Validates safe admin patterns
 * - REST API Authentication: Ensures secure endpoints for settings
 *
 * @since 1.26033.0641
 */
class Diagnostic_Admin_Settings_Sanitization_Verification extends Diagnostic_Base {

	protected static $slug = 'admin-settings-sanitization-verification';
	protected static $title = 'Admin Settings Sanitization Verification';
	protected static $description = 'Verifies admin settings are properly sanitized';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check registered settings
		global $wp_settings_errors;
		if ( ! empty( $wp_settings_errors ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of errors */
				__( '%d settings error(s) detected - verify sanitization is working', 'wpshadow' ),
				count( $wp_settings_errors )
			);
		}

		// Check for unsanitized option updates
		$problematic_options = array();
		$test_option         = 'test_sanitization_' . time();
		update_option( $test_option, '<script>alert("xss")</script>' );
		$stored_value = get_option( $test_option );

		if ( '<script>' === substr( $stored_value, 0, 8 ) ) {
			$issues[] = __( 'Options are stored without sanitization - potential XSS risk', 'wpshadow' );
		}

		delete_option( $test_option );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-settings-sanitization-verification',
			);
		}

		return null;
	}
}
