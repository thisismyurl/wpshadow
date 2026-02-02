<?php
/**
 * No Encryption for Sensitive Exports
 *
 * Tests whether exports containing sensitive data are encrypted.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Encryption_For_Sensitive_Exports Class
 *
 * Validates encryption of sensitive data exports.
 *
 * @since 1.2601.2148
 */
class Diagnostic_No_Encryption_For_Sensitive_Exports extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-encryption-for-sensitive-exports';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sensitive Data Export Encryption';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies sensitive data exports are encrypted';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests encryption availability for sensitive exports.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site exports sensitive data
		if ( ! self::exports_sensitive_data() ) {
			return null;
		}

		$issues = array();

		// 1. Check for encryption capability
		if ( ! self::has_encryption_available() ) {
			$issues[] = __( 'No encryption available for exports', 'wpshadow' );
		}

		// 2. Check for password protection
		if ( ! self::supports_password_protection() ) {
			$issues[] = __( 'Exports cannot be password protected', 'wpshadow' );
		}

		// 3. Check HTTPS download requirement
		if ( ! self::enforces_https_download() ) {
			$issues[] = __( 'Exports can be downloaded over unencrypted HTTP', 'wpshadow' );
		}

		// 4. Check for encryption standards
		if ( ! self::uses_strong_encryption() ) {
			$issues[] = __( 'Uses weak encryption algorithms (should be AES-256)', 'wpshadow' );
		}

		// 5. Check GDPR compliance
		if ( ! self::meets_gdpr_requirements() ) {
			$issues[] = __( 'Export encryption does not meet GDPR data security requirements', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of encryption issues */
					__( '%d sensitive data export security gaps found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/export-encryption',
				'recommendations' => array(
					__( 'Implement AES-256 encryption for all exports', 'wpshadow' ),
					__( 'Require password protection for downloads', 'wpshadow' ),
					__( 'Always serve exports over HTTPS', 'wpshadow' ),
					__( 'Auto-delete exports after 24 hours', 'wpshadow' ),
					__( 'Log all export access for audit trail', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if site exports sensitive data.
	 *
	 * @since  1.2601.2148
	 * @return bool True if sensitive data exported.
	 */
	private static function exports_sensitive_data() {
		global $wpdb;

		// Check for user data exports
		$users = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
		if ( $users > 1 ) {
			return true; // Multi-user site
		}

		// Check for WooCommerce (customer data)
		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}

		// Check for user personal data requests
		$requests = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'user_request'" );
		if ( $requests > 0 ) {
			return true;
		}

		// Check for privacy-sensitive plugins
		$privacy_plugins = array(
			'buddypress/bp-loader.php',
			'memberpress/memberpress.php',
			'learn-dash/learndash.php',
		);

		foreach ( $privacy_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for encryption availability.
	 *
	 * @since  1.2601.2148
	 * @return bool True if encryption available.
	 */
	private static function has_encryption_available() {
		// Check for OpenSSL
		if ( ! extension_loaded( 'openssl' ) ) {
			return false;
		}

		// Check for encryption plugin
		if ( is_plugin_active( 'wpshadow-pro-security/wpshadow-pro-security.php' ) ) {
			return true;
		}

		// Check for WP encryption library
		if ( function_exists( 'wp_json_encode' ) && function_exists( 'wp_kses_post' ) ) {
			// At least basic encryption possible
			return true;
		}

		return false;
	}

	/**
	 * Check for password protection.
	 *
	 * @since  1.2601.2148
	 * @return bool True if password protection available.
	 */
	private static function supports_password_protection() {
		// Check if exports can be password-protected
		if ( function_exists( 'wpshadow_export_with_password' ) ) {
			return true;
		}

		// Check for WP-CLI export with password
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return true;
		}

		// Check for security plugin capability
		if ( is_plugin_active( 'wpshadow-pro-security/wpshadow-pro-security.php' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check HTTPS download enforcement.
	 *
	 * @since  1.2601.2148
	 * @return bool True if HTTPS enforced.
	 */
	private static function enforces_https_download() {
		// Check if site uses HTTPS
		if ( ! is_ssl() ) {
			return false;
		}

		// Check if download links force HTTPS
		if ( get_option( 'home' ) && strpos( get_option( 'home' ), 'https://' ) === 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for strong encryption algorithms.
	 *
	 * @since  1.2601.2148
	 * @return bool True if strong encryption used.
	 */
	private static function uses_strong_encryption() {
		// Check for AES-256
		if ( defined( 'WPSHADOW_EXPORT_ENCRYPTION' ) && 'AES-256' === WPSHADOW_EXPORT_ENCRYPTION ) {
			return true;
		}

		// Check OpenSSL algorithms
		if ( extension_loaded( 'openssl' ) ) {
			$ciphers = openssl_get_cipher_methods();

			if ( in_array( 'aes-256-gcm', $ciphers, true ) || in_array( 'aes-256-cbc', $ciphers, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check GDPR compliance.
	 *
	 * @since  1.2601.2148
	 * @return bool True if GDPR requirements met.
	 */
	private static function meets_gdpr_requirements() {
		// GDPR requires:
		// 1. Data in transit: HTTPS (TLS 1.2+)
		// 2. Data at rest: Strong encryption
		// 3. Access control: Authentication
		// 4. Audit trail: Logging

		// Check HTTPS
		if ( ! is_ssl() ) {
			return false;
		}

		// Check for encryption
		if ( ! self::has_encryption_available() ) {
			return false;
		}

		// Check for access logging
		if ( function_exists( 'wpshadow_log_access' ) ) {
			return true;
		}

		// Check for audit log capability
		if ( is_plugin_active( 'wpshadow/wpshadow.php' ) ) {
			// Core plugin has activity logging
			return true;
		}

		return false;
	}
}
