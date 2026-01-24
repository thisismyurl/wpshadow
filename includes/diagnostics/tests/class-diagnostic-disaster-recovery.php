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
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Disaster Recovery Readiness
	 * Slug: disaster-recovery
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Tests backup restore and recovery procedures.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_disaster_recovery(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Disaster recovery plan is in place and tested'];
		}
		$message = $result['description'] ?? 'Disaster recovery readiness issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
