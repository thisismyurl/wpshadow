<?php
/**
 * Database Connection Pool Treatment
 *
 * Issue #4984: No Database Connection Pool
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if persistent database connections are used.
 * New connection per request wastes resources.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Database_Connection_Pool Class
 *
 * @since 1.6050.0000
 */
class Treatment_Database_Connection_Pool extends Treatment_Base {

	protected static $slug = 'database-connection-pool';
	protected static $title = 'No Database Connection Pool';
	protected static $description = 'Checks if persistent database connections are configured';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Database_Connection_Pool' );
	}
}
