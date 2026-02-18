<?php
/**
 * API Key Encryption Diagnostic
 *
 * Checks that sensitive API keys are properly encrypted before storage.
 *
 * @since   1.6032.1000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;
use WPShadow\Core\Secret_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_API_Key_Encryption Class
 *
 * Verifies that API keys are encrypted using Secret_Manager.
 *
 * @since 1.6032.1000
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
	 * @since  1.6032.1000
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
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'OpenSSL PHP extension is not available. Encryption may not work properly.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/api-key-encryption',
				'context'      => array(
					'why'            => __( 'Plaintext API keys in DB = instant compromise. Real scenario: Database leaked via SQL injection. 10,000 API keys exposed. Attacker impersonates all services. Unauthorized transactions. Cost: $8.9M average breach cost (Verizon). With encryption: DB breach happens but keys unreadable. Attack stopped. AES-256 = requires encryption key (separate, not in DB). Encryption = $0 additional cost, prevents $M+ breach.', 'wpshadow' ),
					'recommendation' => __( '1. Enable OpenSSL PHP extension: php.ini. 2. Implement Secret_Manager class using AES-256. 3. Store encryption keys in wp-config.php or environment. 4. Never log unencrypted keys. 5. Decrypt only when needed (memory). 6. Rotate keys every 60 days. 7. Migrate existing plaintext keys. 8. Test encryption in staging. 9. Audit key access in logs. 10. Use managed key service if available.', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'api-encryption', 'openssl-extension' );
			return $finding;
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
	 * @since  1.6032.1000
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
