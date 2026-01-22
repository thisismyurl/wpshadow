<?php
declare(strict_types=1);
/**
 * Database Backup Verification Diagnostic
 *
 * Philosophy: Show value - verify backups actually work
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if database backups are functioning.
 */
class Diagnostic_Database_Backup_Verification extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for last successful backup timestamp from common plugins
		$backup_options = array(
			'updraftplus_last_backup',
			'backwpup_last_run',
			'backup_last_success',
		);
		
		$last_backup = 0;
		foreach ( $backup_options as $option ) {
			$time = get_option( $option, 0 );
			if ( $time > $last_backup ) {
				$last_backup = $time;
			}
		}
		
		// If no backup in 7 days
		if ( $last_backup === 0 || ( time() - $last_backup ) > ( 7 * DAY_IN_SECONDS ) ) {
			return array(
				'id'          => 'database-backup-verification',
				'title'       => 'Database Backup Not Verified',
				'description' => 'No successful database backup detected in the last 7 days. Verify your backup plugin is functioning correctly.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/verify-wordpress-backups/',
				'training_link' => 'https://wpshadow.com/training/backup-verification/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}
		
		return null;
	}
}
