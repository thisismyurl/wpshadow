<?php declare(strict_types=1);
/**
 * Database Error Display Diagnostic
 *
 * Philosophy: Information disclosure - hide database errors
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if database errors are displayed to visitors.
 */
class Diagnostic_Database_Error_Display {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		// Check if show_errors is enabled
		if ( $wpdb->show_errors ) {
			return array(
				'id'          => 'database-error-display',
				'title'       => 'Database Errors Displayed to Public',
				'description' => 'Database errors are being displayed to visitors, potentially revealing database structure, table names, and credentials. Disable WP_DEBUG_DISPLAY in production.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/hide-database-errors/',
				'training_link' => 'https://wpshadow.com/training/database-security/',
				'auto_fixable' => true,
				'threat_level' => 70,
			);
		}
		
		return null;
	}
}
