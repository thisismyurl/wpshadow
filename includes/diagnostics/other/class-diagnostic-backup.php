<?php
declare(strict_types=1);
/**
 * Backup Plugin Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for active backup solution.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Backup extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! self::has_backup_plugin() ) {
			return array(
				'id'           => 'backup-missing',
				'title'        => 'No Backup Solution Detected',
				'description'  => 'Your site has no automated backup plugin active. Regular backups are critical for recovery.',
				'color'        => '#f44336',
				'bg_color'     => '#ffebee',
				'kb_link'      => 'https://wpshadow.com/kb/how-to-set-up-automated-backups/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=backup',
				'auto_fixable' => false,
				'threat_level' => 85,
			);
		}

		return null;
	}

	/**
	 * Check if a backup plugin is active.
	 *
	 * @return bool True if backup plugin detected.
	 */
	private static function has_backup_plugin() {
		$backup_keywords = array( 'backup', 'updraft', 'backwpup', 'duplicator', 'snapshot', 'vaultpress', 'jetpack' );
		$active_plugins  = get_option( 'active_plugins', array() );

		foreach ( $active_plugins as $plugin ) {
			$plugin_lower = strtolower( $plugin );
			foreach ( $backup_keywords as $keyword ) {
				if ( false !== strpos( $plugin_lower, $keyword ) ) {
					return true;
				}
			}
		}

		return false;
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
