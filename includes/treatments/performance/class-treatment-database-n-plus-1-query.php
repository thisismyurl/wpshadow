<?php
/**
 * Database N+1 Query Problem Treatment
 *
 * Detects N+1 query patterns causing performance issues.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2056
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database N+1 Query Problem Treatment Class
 *
 * Detects N+1 query patterns where loops trigger repeated
 * similar queries instead of batch loading.
 *
 * @since 1.6033.2056
 */
class Treatment_Database_N_Plus_1_Query extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-n-plus-1-query';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Database N+1 Query Problem';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects N+1 query patterns causing excessive database calls';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes query patterns to detect N+1 problems.
	 * Common in post loops fetching meta/terms repeatedly.
	 *
	 * @since  1.6033.2056
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Database_N_Plus_1_Query' );
	}
}
