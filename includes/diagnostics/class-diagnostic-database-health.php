<?php declare(strict_types=1);
/**
 * Database Health Diagnostic
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Monitor database performance issues.
 */
class Diagnostic_Database_Health extends Diagnostic_Base {

	protected static $slug        = 'database-health';
	protected static $title       = 'Database Performance Issues';
	protected static $description = 'Your database has performance issues that need attention.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check database connection
		if ( ! $wpdb->check_connection( false ) ) {
			return array(
				'title'       => 'Database Connection Failed',
				'description' => 'Unable to connect to the database. Check your database credentials and server status.',
				'severity'    => 'high',
				'category'    => 'stability',
			);
		}

		// Check autoloaded data size
		$autoload_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(option_value)) as autoload_size 
			FROM {$wpdb->options} 
			WHERE autoload = 'yes'"
		);

		$autoload_threshold = 1 * 1048576; // 1MB
		if ( $autoload_size > $autoload_threshold ) {
			$issues[] = sprintf(
				'Large autoloaded data: %s MB (slows page load)',
				round( $autoload_size / 1048576, 2 )
			);
		}

		// Check expired transients
		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				'%_transient_timeout_%',
				time()
			)
		);

		if ( $expired_transients > 100 ) {
			$issues[] = sprintf(
				'%d expired transients need cleanup',
				$expired_transients
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'title'       => self::$title,
				'description' => implode( '. ', $issues ) . '. Run database optimization to improve performance.',
				'severity'    => 'medium',
				'category'    => 'performance',
			);
		}

		return null;
	}
}
