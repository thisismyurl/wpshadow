<?php
/**
 * Migrate Plaintext Secrets to Encrypted Storage
 *
 * One-time utility to migrate existing plaintext secrets to encrypted storage.
 * Run this via WP-CLI or admin panel after upgrading.
 *
 * Usage (WP-CLI):
 * wp eval 'require_once "wp-content/plugins/wpshadow/includes/core/class-secret-migration.php"; WPShadow\Core\Secret_Migration::migrate_all();'
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.26032.1000
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Secret Migration Class
 *
 * Safely migrates plaintext secrets to encrypted storage.
 *
 * @since 1.26032.1000
 */
class Secret_Migration {

	/**
	 * Migrate all known plaintext secrets to encrypted storage
	 *
	 * @since  1.26032.1000
	 * @return array Migration results.
	 */
	public static function migrate_all(): array {
		$results = array(
			'migrated' => array(),
			'skipped'  => array(),
			'errors'   => array(),
		);

		// List of secrets to migrate (old option name => new key name)
		$secrets_to_migrate = array(
			'wpshadow_webhook_secret'   => 'webhook_secret',
			'wpshadow_vault_api_key'    => 'vault_api_key',
			'wpshadow_guardian_api_key' => 'guardian_api_key',
		);

		foreach ( $secrets_to_migrate as $old_option => $new_key ) {
			$result = self::migrate_secret( $old_option, $new_key );
			if ( $result['migrated'] ) {
				$results['migrated'][] = $new_key;
			} elseif ( $result['skipped'] ) {
				$results['skipped'][] = $new_key;
			} else {
				$results['errors'][] = array(
					'key'    => $new_key,
					'reason' => $result['reason'] ?? 'Unknown error',
				);
			}
		}

		return $results;
	}

	/**
	 * Migrate a single plaintext secret
	 *
	 * @since  1.26032.1000
	 * @param  string $old_option Old plaintext option name.
	 * @param  string $new_key    New encrypted key name.
	 * @return array {
	 *     Migration result.
	 *     @type bool   $migrated Whether secret was migrated.
	 *     @type bool   $skipped  Whether secret was skipped (not found or already encrypted).
	 *     @type string $reason   Optional error reason.
	 * }
	 */
	private static function migrate_secret( string $old_option, string $new_key ): array {
		// Check if already encrypted
		if ( Secret_Manager::exists( $new_key ) ) {
			return array( 'migrated' => false, 'skipped' => true );
		}

		// Get plaintext value
		$plaintext = get_option( $old_option );
		if ( empty( $plaintext ) ) {
			return array( 'migrated' => false, 'skipped' => true );
		}

		// Encrypt and store
		if ( Secret_Manager::store( $new_key, $plaintext ) ) {
			// Log the migration
			Secret_Audit_Log::log_access( $new_key, 'migrated_from_plaintext' );

			// Delete old plaintext option
			delete_option( $old_option );

			return array( 'migrated' => true, 'skipped' => false );
		}

		return array(
			'migrated' => false,
			'skipped'  => false,
			'reason'   => 'Failed to encrypt and store secret',
		);
	}

	/**
	 * Verify all secrets are encrypted
	 *
	 * @since  1.26032.1000
	 * @return array {
	 *     Verification results.
	 *     @type bool  $all_encrypted   Whether all secrets are encrypted.
	 *     @type array $unencrypted     List of unencrypted secrets found.
	 *     @type array $encrypted       List of encrypted secrets found.
	 * }
	 */
	public static function verify_all_encrypted(): array {
		$plaintext_options = array(
			'wpshadow_webhook_secret',
			'wpshadow_vault_api_key',
			'wpshadow_guardian_api_key',
		);

		$encrypted_keys = array(
			'webhook_secret',
			'vault_api_key',
			'guardian_api_key',
		);

		$unencrypted = array();
		$encrypted = array();

		// Check for plaintext
		foreach ( $plaintext_options as $option ) {
			if ( get_option( $option ) ) {
				$unencrypted[] = $option;
			}
		}

		// Check for encrypted
		foreach ( $encrypted_keys as $key ) {
			if ( Secret_Manager::exists( $key ) ) {
				$encrypted[] = $key;
			}
		}

		return array(
			'all_encrypted'  => empty( $unencrypted ),
			'unencrypted'    => $unencrypted,
			'encrypted'      => $encrypted,
		);
	}
}
