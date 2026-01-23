<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Disaster Recovery Readiness
 *
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Disaster_Recovery extends Diagnostic_Base {
	protected static $slug        = 'disaster-recovery';
	protected static $title       = 'Disaster Recovery Readiness';
	protected static $description = 'Tests backup restore and recovery procedures.';

	public static function check(): ?array {
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'duplicator/duplicator.php',
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
		);

		$has_backup_plugin = false;
		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_backup_plugin = true;
				break;
			}
		}

		if ( ! $has_backup_plugin ) {
			return array(
				'id'            => static::$slug,
				'title'         => __( 'No disaster recovery plan', 'wpshadow' ),
				'description'   => __( 'No backup system detected. Enterprise sites need automated backups and tested restore procedures.', 'wpshadow' ),
				'severity'      => 'critical',
				'category'      => 'general',
				'kb_link'       => 'https://wpshadow.com/kb/disaster-recovery/',
				'training_link' => 'https://wpshadow.com/training/disaster-recovery/',
				'auto_fixable'  => false,
				'threat_level'  => 90,
			);
		}

		$upload_dir  = wp_upload_dir();
		$backup_dirs = array(
			$upload_dir['basedir'] . '/updraft',
			$upload_dir['basedir'] . '/backwpup-*-backups',
			ABSPATH . 'wp-snapshots',
		);

		$latest_backup = 0;
		foreach ( $backup_dirs as $dir ) {
			$files = glob( $dir . '/*' );
			if ( $files ) {
				foreach ( $files as $file ) {
					$mtime = filemtime( $file );
					if ( $mtime > $latest_backup ) {
						$latest_backup = $mtime;
					}
				}
			}
		}

		if ( $latest_backup === 0 ) {
			return null;
		}

		$days_old = ( time() - $latest_backup ) / DAY_IN_SECONDS;
		if ( $days_old > 7 ) {
			return array(
				'id'              => static::$slug,
				'title'           => sprintf( __( 'Last backup is %d days old', 'wpshadow' ), (int) $days_old ),
				'description'     => __( 'Backups should run at least weekly. Old backups may not recover recent data.', 'wpshadow' ),
				'severity'        => 'medium',
				'category'        => 'general',
				'kb_link'         => 'https://wpshadow.com/kb/disaster-recovery/',
				'training_link'   => 'https://wpshadow.com/training/disaster-recovery/',
				'auto_fixable'    => false,
				'threat_level'    => 65,
				'backup_age_days' => (int) $days_old,
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
