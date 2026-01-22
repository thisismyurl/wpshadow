<?php
declare(strict_types=1);
/**
 * Database User Privileges Diagnostic
 *
 * Philosophy: Principle of least privilege - limit database damage
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check database user privileges.
 */
class Diagnostic_Database_Privileges extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Query current user privileges
		$grants = $wpdb->get_results( "SHOW GRANTS FOR CURRENT_USER", ARRAY_N );
		
		if ( empty( $grants ) ) {
			return null; // Can't check
		}
		
		$dangerous_privileges = array( 'DROP', 'CREATE USER', 'GRANT OPTION', 'SUPER', 'SHUTDOWN' );
		$has_dangerous = array();
		
		foreach ( $grants as $grant ) {
			$grant_text = $grant[0];
			foreach ( $dangerous_privileges as $priv ) {
				if ( stripos( $grant_text, $priv ) !== false ) {
					$has_dangerous[] = $priv;
				}
			}
		}
		
		if ( ! empty( $has_dangerous ) ) {
			return array(
				'id'          => 'database-privileges',
				'title'       => 'Excessive Database Privileges',
				'description' => sprintf(
					'Your database user has dangerous privileges: %s. WordPress only needs SELECT, INSERT, UPDATE, DELETE. Limit privileges to reduce SQL injection impact.',
					implode( ', ', array_unique( $has_dangerous ) )
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/limit-database-privileges/',
				'training_link' => 'https://wpshadow.com/training/database-security/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}
