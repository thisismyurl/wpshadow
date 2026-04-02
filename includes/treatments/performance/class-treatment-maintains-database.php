<?php
/**
 * Database Maintenance Scheduled Treatment
 *
 * Tests if database optimization is run regularly.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Maintenance Scheduled Treatment Class
 *
 * Verifies that database maintenance tasks are scheduled.
 *
 * @since 1.6093.1200
 */
class Treatment_Maintains_Database extends Treatment_Base {

	protected static $slug = 'maintains-database';
	protected static $title = 'Database Maintenance Scheduled';
	protected static $description = 'Tests if database optimization is run regularly';
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Maintains_Database' );
	}
}
