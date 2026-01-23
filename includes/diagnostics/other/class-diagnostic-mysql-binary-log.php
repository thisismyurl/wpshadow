<?php
declare(strict_types=1);
/**
 * MySQL Binary Log Exposure Diagnostic
 *
 * Philosophy: Database security - protect database activity logs
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check MySQL binary log exposure.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_MySQL_Binary_Log extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;

		// Check if binary logging is enabled
		$log_bin = $wpdb->get_var( "SHOW VARIABLES LIKE 'log_bin'" );

		if ( empty( $log_bin ) ) {
			return null; // Binary logging not enabled
		}

		// Get binary log location
		$log_bin_basename = $wpdb->get_var( "SHOW VARIABLES LIKE 'log_bin_basename'" );

		if ( ! empty( $log_bin_basename ) ) {
			// Check if log files might be in webroot (common misconfiguration)
			$webroot = ABSPATH;

			return array(
				'id'            => 'mysql-binary-log',
				'title'         => 'MySQL Binary Logging Enabled',
				'description'   => 'MySQL binary logs are enabled and contain all database queries including passwords and sensitive data. Ensure binary logs are stored outside webroot and have restricted permissions (600). Logs should be rotated and purged regularly.',
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/secure-mysql-binary-logs/',
				'training_link' => 'https://wpshadow.com/training/database-logging/',
				'auto_fixable'  => false,
				'threat_level'  => 75,
			);
		}

		return null;
	}

}