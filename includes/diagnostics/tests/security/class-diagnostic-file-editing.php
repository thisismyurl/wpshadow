<?php
/**
 * Diagnostic: File Editing Enabled
 *
 * Checks if theme and plugin file editors are enabled in wp-admin.
 * If an attacker compromises an admin account, they can inject malicious code.
 *
 * Philosophy: Defense in depth (#1 helpful neighbor), inspire confidence (#8)
 * KB Link: https://wpshadow.com/kb/security-file-editing
 * Training: https://wpshadow.com/training/security-file-editing
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File Editing Diagnostic Class
 *
 * Detects if theme/plugin file editors are enabled (security risk).
 */
class Diagnostic_File_Editing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-editing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme and Plugin File Editors Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'WordPress file editors are enabled, allowing code injection if admin account is compromised.';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Check if DISALLOW_FILE_EDIT is defined and set to true
		if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
			return null; // File editing is disabled, all good
		}

		// Check if DISALLOW_FILE_MODS is defined (stronger - disables all modifications)
		if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
			return null; // All file modifications disabled, even better
		}

		// File editing is enabled - this is a security risk
		$threat_level = 70;

		// Check if there are recent admin logins from unusual locations (if tracking available)
		$recent_admin_logins = get_option( 'wpshadow_recent_admin_logins', array() );
		if ( ! empty( $recent_admin_logins ) ) {
			$threat_level += 5;
		}

		// Check if there are multiple admin users
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		if ( count( $admin_users ) > 3 ) {
			$threat_level += 5;
		}

		$message = __( 'The WordPress theme and plugin file editors are currently enabled. If an attacker gains access to an administrator account (through phishing, weak passwords, or session hijacking), they can inject malicious code directly through the WordPress admin panel. Disabling these editors adds an important security layer.', 'wpshadow' );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $message,
			'severity'    => 'high',
			'threat_level' => min( $threat_level, 100 ),
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/security-file-editing',
			'training_link' => 'https://wpshadow.com/training/security-file-editing',
			'impact'      => array(
				'security' => __( 'Reduces attack surface if admin account is compromised', 'wpshadow' ),
				'usability' => __( 'Disabling prevents accidental file corruption through web editor', 'wpshadow' ),
			),
			'evidence'    => array(
				'DISALLOW_FILE_EDIT' => defined( 'DISALLOW_FILE_EDIT' ) ? ( DISALLOW_FILE_EDIT ? 'true' : 'false' ) : 'undefined',
				'DISALLOW_FILE_MODS' => defined( 'DISALLOW_FILE_MODS' ) ? ( DISALLOW_FILE_MODS ? 'true' : 'false' ) : 'undefined',
				'admin_user_count'   => count( $admin_users ),
			),
		);
	}

	/**
	 * Get available treatments for this diagnostic
	 *
	 * @since  1.2601.2148
	 * @return array Array of treatment class names.
	 */
	public static function get_available_treatments(): array {
		return array(
			'WPShadow\\Treatments\\Treatment_File_Editing',
		);
	}
}
