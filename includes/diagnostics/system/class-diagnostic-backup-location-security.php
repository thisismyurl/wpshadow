<?php
declare(strict_types=1);
/**
 * Backup Location Security Diagnostic
 *
 * Philosophy: Data protection - secure backup storage
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if backups are stored securely outside web root.
 */
class Diagnostic_Backup_Location_Security extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$upload_dir = wp_upload_dir();
		
		// Check for backup files in web-accessible directory
		$backup_files = glob( $upload_dir['basedir'] . '/*.{zip,tar,gz,sql}', GLOB_BRACE );
		
		if ( ! empty( $backup_files ) ) {
			return array(
				'id'          => 'backup-location-security',
				'title'       => 'Backup Files in Web-Accessible Directory',
				'description' => sprintf(
					'Found %d backup files in uploads directory (web-accessible). Attackers can download complete site backups including database. Store backups outside web root or protect with .htaccess.',
					count( $backup_files )
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-backup-storage/',
				'training_link' => 'https://wpshadow.com/training/backup-security/',
				'auto_fixable' => false,
				'threat_level' => 90,
			);
		}
		
		return null;
	}
}
