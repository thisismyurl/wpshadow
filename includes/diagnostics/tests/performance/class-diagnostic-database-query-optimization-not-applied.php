<?php
/**
 * Database Query Optimization Not Applied Diagnostic
 *
 * Checks query optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Query_Optimization_Not_Applied Class
 *
 * Performs diagnostic check for Database Query Optimization Not Applied.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Database_Query_Optimization_Not_Applied extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-optimization-not-applied';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Optimization Not Applied';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks query optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if database query optimization is applied
		if ( ! has_filter( 'init', 'optimize_db_queries' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database query optimization not applied. Use query caching, proper indexes, and optimize slow queries for better performance.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-query-optimization-not-applied',
			);
		}

		return null;
	}
}
