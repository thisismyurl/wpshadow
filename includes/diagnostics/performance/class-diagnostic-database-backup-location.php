<?php
declare(strict_types=1);
/**
 * Database Backup Location Diagnostic
 *
 * Philosophy: Backup security - protect database dumps
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if database backups are in webroot.
 */
class Diagnostic_Database_Backup_Location extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Common backup file patterns
		$patterns = array(
			'*.sql',
			'*.sql.gz',
			'*.sql.zip',
			'*.db',
			'backup*.sql',
			'dump*.sql',
			'database*.sql',
		);
		
		$found_backups = array();
		
		foreach ( $patterns as $pattern ) {
			$files = glob( ABSPATH . $pattern );
			if ( ! empty( $files ) ) {
				foreach ( $files as $file ) {
					$found_backups[] = basename( $file );
				}
			}
			
			// Also check wp-content
			$files = glob( WP_CONTENT_DIR . '/' . $pattern );
			if ( ! empty( $files ) ) {
				foreach ( $files as $file ) {
					$found_backups[] = 'wp-content/' . basename( $file );
				}
			}
		}
		
		if ( ! empty( $found_backups ) ) {
			return array(
				'id'          => 'database-backup-location',
				'title'       => 'Database Backups in Web Root',
				'description' => sprintf(
					'Database backup files found in web-accessible directories: %s. These files contain your entire database including passwords. Move backups outside webroot or delete immediately.',
					implode( ', ', array_slice( $found_backups, 0, 5 ) )
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-database-backups/',
				'training_link' => 'https://wpshadow.com/training/backup-security/',
				'auto_fixable' => true,
				'threat_level' => 85,
			);
		}
		
		return null;
	}
}
