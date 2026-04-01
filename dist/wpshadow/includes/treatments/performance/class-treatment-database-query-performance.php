<?php
/**
 * Database Query Performance and Indexing
 *
 * Validates database query performance and index optimization.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Database_Query_Performance Class
 *
 * Checks database query performance and indexing issues.
 *
 * @since 0.6093.1200
 */
class Treatment_Database_Query_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates database query performance and index usage';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Database_Query_Performance' );
	}
}
