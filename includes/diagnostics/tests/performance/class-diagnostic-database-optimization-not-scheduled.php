<?php
/**
 * Database Optimization Not Scheduled Diagnostic
 *
 * Checks if database optimization is scheduled.
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
 * Database Optimization Not Scheduled Diagnostic Class
 *
 * Detects unscheduled database optimization.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Database_Optimization_Not_Scheduled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-optimization-not-scheduled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Optimization Not Scheduled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database optimization is scheduled';

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
		// Check if database optimization is scheduled
		if ( ! wp_next_scheduled( 'wpshadow_optimize_database' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database optimization is not scheduled. Schedule weekly database optimization to remove transients, revisions, and unused tables.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/database-optimization-not-scheduled',
			);
		}

		return null;
	}
}
