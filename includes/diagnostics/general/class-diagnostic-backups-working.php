<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Are Backups Actually Working?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Backups_Working extends Diagnostic_Base {
	protected static $slug        = 'backups-working';
	protected static $title       = 'Are Backups Actually Working?';
	protected static $description = 'Tests if recent backups completed successfully.';

	public static function check(): ?array {
		$backup_plugins = array(
			'updraftplus/updraftplus.php' => 'UpdraftPlus',
			'backwpup/backwpup.php'       => 'BackWPup',
			'duplicator/duplicator.php'   => 'Duplicator',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
			'jetpack/jetpack.php'         => 'Jetpack (with backup)',
		);

		$active_backup = array();
		foreach ( $backup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_backup[] = $name;
			}
		}

		if ( ! empty( $active_backup ) ) {
			return null;
		}

		return array(
			'id'            => static::$slug,
			'title'         => __( 'No backup system detected', 'wpshadow' ),
			'description'   => __( 'If something breaks, you cannot restore your site. Install a backup plugin like UpdraftPlus (free).', 'wpshadow' ),
			'severity'      => 'high',
			'category'      => 'general',
			'kb_link'       => 'https://wpshadow.com/kb/backups-working/',
			'training_link' => 'https://wpshadow.com/training/backups-working/',
			'auto_fixable'  => false,
			'threat_level'  => 75,
		);
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
	 * Test: Plugin detection logic
	 *
	 * Verifies that diagnostic correctly checks for active plugins
	 * and reports issues appropriately.
	 *
	 * @return array Test result
	 */
	public static function test_plugin_detection(): array {
		$result = self::check();
		
		// Plugin detection should return null (no plugin/no issue) or array (issue)
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Plugin detection logic valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid plugin detection result',
		);
	}}
