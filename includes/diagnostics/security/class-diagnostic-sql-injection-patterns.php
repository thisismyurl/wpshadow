<?php
declare(strict_types=1);
/**
 * SQL Injection Pattern Detection Diagnostic
 *
 * Philosophy: Database security - prevent SQL injection
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for SQL injection vulnerabilities.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SQL_Injection_Patterns extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		$plugins_dir = WP_PLUGIN_DIR;
		$files = glob( $plugins_dir . '/*/*.php' );
		
		foreach ( $files as $file ) {
			$content = file_get_contents( $file );
			
			// Look for raw SQL with user input
			if ( preg_match( '/query\s*\(\s*["\']SELECT.*\$_(?:GET|POST|REQUEST)/', $content ) ) {
				return array(
					'id'          => 'sql-injection-patterns',
					'title'       => 'SQL Injection Vulnerability Pattern Found',
					'description' => 'Code concatenates user input into SQL queries. This allows SQL injection attacks. Always use $wpdb->prepare().',
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/prevent-sql-injection/',
					'training_link' => 'https://wpshadow.com/training/sql-safety/',
					'auto_fixable' => false,
					'threat_level' => 95,
				);
			}
		}
		
		return null;
	}

}