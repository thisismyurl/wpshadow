<?php
/**
 * Database Query Optimization Not Implemented Diagnostic
 *
 * Checks if database queries are optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Query Optimization Not Implemented Diagnostic Class
 *
 * Detects unoptimized database queries.
 *
 * @since 1.2601.2315
 */
class Diagnostic_Database_Query_Optimization_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-optimization-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Optimization Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if queries are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if database profiler plugins are active
		$optimizer_plugins = array(
			'wp-query-monitor/wp-query-monitor.php',
			'debug-bar/debug-bar.php',
		);

		$optimizer_active = false;
		foreach ( $optimizer_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$optimizer_active = true;
				break;
			}
		}

		if ( ! $optimizer_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No database query optimization tool is active. Use Query Monitor or similar to identify slow queries.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-query-optimization-not-implemented',
			);
		}

		return null;
	}
}
