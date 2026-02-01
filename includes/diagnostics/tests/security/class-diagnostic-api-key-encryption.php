<?php
/**
 * API Key Encryption Diagnostic
 *
 * Checks that sensitive API keys are properly encrypted before storage.
 *
 * @since   1.26032.1000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Secret_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_API_Key_Encryption Class
 *
 * Verifies that API keys are encrypted using Secret_Manager.
 *
 * @since 1.26032.1000
 */
class Diagnostic_API_Key_Encryption extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-key-encryption';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Key Encryption';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies sensitive API keys are encrypted before database storage';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.26032.1000
	 * @return array|null Finding if encryption is not properly configured.
	 */
	public static function check() {
		// Check if Secret_Manager class exists
		if ( ! class_exists( 'WPShadow\Core\Secret_Manager' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Secret_Manager class not found. API keys may not be encrypted.', 'wpshadow' ),
				'severity'    => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-key-encryption',
			);
		}

		// Check if WordPress OpenSSL support is available
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'OpenSSL PHP extension is not available. Encryption may not work properly.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/enable-openssl-php',
			);
		}

		// Check for plaintext API keys (legacy check)
		$plaintext_keys = self::check_plaintext_api_keys();
		if ( ! empty( $plaintext_keys ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: list of plaintext keys */
					__( 'Found plaintext API keys in database: %s. These should be migrated to encrypted storage.', 'wpshadow' ),
					implode( ', ', $plaintext_keys )
				),
				'severity'    => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/migrate-api-keys',
			);
		}

		// All checks passed
		return null;
	}

	/**
	 * Check for plaintext API keys in database
	 *
	 * @since  1.26032.1000
	 * @return array Array of plaintext key option names found.
	 */
	private static function check_plaintext_api_keys(): array {
		$plaintext_keys = array();

		// List of potentially problematic plaintext options
		$dangerous_options = array(
			'wpshadow_github_token',
			'wpshadow_webhook_secret',
			'wpshadow_vault_key',
			'wpshadow_guardian_api_key',
			'wpshadow_api_key',
		);

		global $wpdb;

		foreach ( $dangerous_options as $option ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table and column names cannot be prepared
			$value = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s AND option_value != '' LIMIT 1",
					$option
				)
			);

			// Check if value looks like a plaintext API key (very basic heuristic)
			if ( ! empty( $value ) ) {
				// Encrypted values are typically base64 and/or longer
				// Plaintext keys from GitHub start with 'ghp_', 'ghs_', etc.
				if ( strpos( $value, 'ghp_' ) === 0 || strpos( $value, 'ghs_' ) === 0 ) {
					$plaintext_keys[] = $option;
				}
			}
		}

		return $plaintext_keys;
	}
}
