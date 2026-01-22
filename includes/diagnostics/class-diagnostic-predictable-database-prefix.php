<?php declare(strict_types=1);
/**
 * Predictable Database Prefix Diagnostic
 *
 * Philosophy: Obscurity - change default database prefix
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if database prefix is default.
 */
class Diagnostic_Predictable_Database_Prefix {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		if ( $wpdb->prefix === 'wp_' ) {
			return array(
				'id'          => 'predictable-database-prefix',
				'title'       => 'Default Database Prefix (wp_)',
				'description' => 'Database tables use default "wp_" prefix. SQL injection attacks know exact table names. Change prefix to random string in wp-config.php before initial setup.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/change-database-prefix/',
				'training_link' => 'https://wpshadow.com/training/database-hardening/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
