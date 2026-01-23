<?php
declare(strict_types=1);
/**
 * MySQL LOAD_FILE Privileges Diagnostic
 *
 * Philosophy: Database security - prevent file system access
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if database user has FILE privilege.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_MySQL_Load_File extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Check user privileges
		$grants = $wpdb->get_results( "SHOW GRANTS FOR CURRENT_USER", ARRAY_N );
		
		if ( empty( $grants ) ) {
			return null;
		}
		
		foreach ( $grants as $grant ) {
			$grant_text = strtoupper( $grant[0] );
			
			// Check for FILE privilege
			if ( strpos( $grant_text, 'FILE' ) !== false ) {
				return array(
					'id'          => 'mysql-load-file',
					'title'       => 'Database User Has FILE Privilege',
					'description' => 'Your database user has FILE privilege, allowing LOAD_FILE() to read any file the MySQL server can access (including /etc/passwd). SQL injection becomes much more dangerous. Remove FILE privilege immediately.',
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/revoke-file-privilege/',
					'training_link' => 'https://wpshadow.com/training/database-privileges/',
					'auto_fixable' => false,
					'threat_level' => 80,
				);
			}
		}
		
		return null;
	}

}