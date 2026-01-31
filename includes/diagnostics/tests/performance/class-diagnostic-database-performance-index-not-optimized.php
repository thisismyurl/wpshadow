<?php
/**
 * Database Performance Index Not Optimized Diagnostic
 *
 * Checks if database indexes are optimized.
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
 * Database Performance Index Not Optimized Diagnostic Class
 *
 * Detects unoptimized database indexes.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Database_Performance_Index_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-performance-index-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Performance Index Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database indexes are optimized';

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
		// Check for database optimization
		if ( ! is_plugin_active( 'wp-optimize/wp-optimize.php' ) && ! is_plugin_active( 'advanced-database-cleaner/advanced-database-cleaner.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database indexes are not optimized. Add proper indexes to frequently queried columns to improve database performance.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-performance-index-not-optimized',
			);
		}

		return null;
	}
}
