<?php declare(strict_types=1);
/**
 * Database Error Exposure Diagnostic
 *
 * Philosophy: Information disclosure - hide database errors
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if database errors are exposed.
 */
class Diagnostic_Database_Error_Exposure {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		if ( defined( 'DB_DEBUG' ) && DB_DEBUG ) {
			return array(
				'id'          => 'database-error-exposure',
				'title'       => 'Database Error Messages Exposed',
				'description' => 'Database debug mode is enabled. SQL errors are displayed to users, revealing database schema and enabling SQL injection attacks. Disable DB_DEBUG in wp-config.php.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/hide-database-errors/',
				'training_link' => 'https://wpshadow.com/training/error-handling/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}
