<?php
/**
 * Admin Settings Sanitization Verification
 *
 * Checks if admin settings are properly sanitized and validated on save.
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
