<?php
/**
 * Database Query Performance Not Optimized Diagnostic
 *
 * Checks if database queries are optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Query Performance Not Optimized Diagnostic Class
 *
 * Detects unoptimized database queries.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Database_Query_Performance_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-performance-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Performance Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database queries are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if query optimization plugin is active
		if ( ! is_plugin_active( 'query-monitor/query-monitor.php' ) && ! is_plugin_active( 'wp-optimize/wp-optimize.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database query performance is not optimized. Use Query Monitor, WP-Optimize, or similar tools to identify and optimize slow queries.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-query-performance-not-optimized',
			);
		}

		return null;
	}
}
