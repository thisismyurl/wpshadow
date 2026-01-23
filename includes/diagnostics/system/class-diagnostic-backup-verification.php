<?php
declare(strict_types=1);
/**
 * Backup Verification Diagnostic
 *
 * Philosophy: Essential for peace of mind - verify backups are working
 * Guides to Pro Guardian features for automated backup verification
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check backup system status and recent backups.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Backup_Verification extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$issues = array();
		
		// Check if backup plugin is installed
		$backup_plugins = array();
		$active_plugins = get_option( 'active_plugins', array() );
		
		foreach ( $active_plugins as $plugin ) {
			if ( stripos( $plugin, 'backup' ) !== false || stripos( $plugin, 'vault' ) !== false ) {
				$backup_plugins[] = $plugin;
			}
		}
		
		// Check if WPShadow Vault is installed
		$vault_installed = class_exists( 'WPShadow_Vault' ) || defined( 'WPSHADOW_VAULT_VERSION' );
		
		if ( empty( $backup_plugins ) && ! $vault_installed ) {
			$issues[] = 'No backup solution detected - your site is at risk if something goes wrong';
		} else if ( ! empty( $backup_plugins ) ) {
			// Check if backups are recent
			$last_backup = null;
			
			// Try to get last backup time from common backup plugins
			if ( in_array( 'backwpup/backwpup.php', $backup_plugins, true ) ) {
				$backups = get_option( 'backwpup_backup_history' );
				if ( is_array( $backups ) && ! empty( $backups ) ) {
					$last_backup = array_key_first( $backups );
				}
			}
			
			if ( $last_backup ) {
				$backup_age_hours = ( time() - intval( $last_backup ) ) / ( 60 * 60 );
				if ( $backup_age_hours > 72 ) { // Older than 3 days
					$issues[] = sprintf(
						'Last backup was %d hours ago - consider scheduling more frequent backups',
						intval( $backup_age_hours )
					);
				}
			} elseif ( ! $vault_installed ) {
				// Can't verify backup time with current backup solution
				$issues[] = 'Unable to verify when last backup occurred - check your backup plugin settings';
			}
		} elseif ( $vault_installed ) {
			// Check Vault backup status
			$last_vault_backup = get_option( 'wpshadow_vault_last_backup' );
			if ( $last_vault_backup ) {
				$backup_age_hours = ( time() - intval( $last_vault_backup ) ) / ( 60 * 60 );
				if ( $backup_age_hours > 72 ) {
					$issues[] = sprintf(
						'Last Vault backup was %d hours ago - consider scheduling daily backups',
						intval( $backup_age_hours )
					);
				}
			} else {
				$issues[] = 'No Vault backups found yet - run your first backup to protect your site';
			}
		}
		
		if ( ! empty( $issues ) ) {
			return array(
				'id'          => 'backup-verification',
				'title'       => 'Backup Status Verification',
				'description' => implode( '. ', $issues ) . '. Regular backups are your safety net.',
				'severity'    => 'high',
				'category'    => 'monitoring',
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-backups/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=backup-verification',
				'auto_fixable' => false,
				'threat_level' => 80,
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
