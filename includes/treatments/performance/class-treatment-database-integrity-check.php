<?php
/**
 * Database Integrity Check Treatment
 *
 * Checks database tables for corruption or issues.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Integrity Check Treatment Class
 *
 * Runs CHECK TABLE on all database tables to detect corruption.
 *
 * @since 1.6093.1200
 */
class Treatment_Database_Integrity_Check extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-integrity-check';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Database Integrity Check';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks database tables for corruption or issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database-health';

	/**
	 * Run the database integrity treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if integrity issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Database_Integrity_Check' );
	}
}
