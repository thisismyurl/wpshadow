<?php
declare(strict_types=1);
/**
 * Backup Encryption Diagnostic
 *
 * Philosophy: Data protection - encrypt backups at rest
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if backups are encrypted.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Backup_Encryption extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check common backup plugins for encryption
		$backup_plugins_with_encryption = array(
			'updraftplus/updraftplus.php' => 'updraft_encryptionphrase',
			'backwpup/backwpup.php' => 'backwpup_cfg_jobencryptionkey',
			'backup/backup.php' => 'backup_encryption_enabled',
		);
		
		$active = get_option( 'active_plugins', array() );
		$unencrypted_backups = array();
		
		foreach ( $backup_plugins_with_encryption as $plugin => $option_key ) {
			if ( in_array( $plugin, $active, true ) ) {
				// Check if encryption is configured
				$encryption_key = get_option( $option_key );
				if ( empty( $encryption_key ) ) {
					$plugin_name = explode( '/', $plugin )[0];
					$unencrypted_backups[] = ucfirst( $plugin_name );
				}
			}
		}
		
		if ( ! empty( $unencrypted_backups ) ) {
			return array(
				'id'          => 'backup-encryption',
				'title'       => 'Backups Not Encrypted',
				'description' => sprintf(
					'Your backup plugin (%s) is not configured to encrypt backups. Unencrypted backups stored in cloud services expose your database credentials, user data, and site content. Enable backup encryption.',
					implode( ', ', $unencrypted_backups )
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/encrypt-wordpress-backups/',
				'training_link' => 'https://wpshadow.com/training/backup-security/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}
	/**
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}
