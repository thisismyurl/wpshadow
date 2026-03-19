<?php
/**
 * Second-Order SQL Injection Treatment
 *
 * Detects second-order SQL injection vulnerabilities where malicious
 * input is stored and later used in unsafe SQL queries.
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
 * Second-Order SQL Injection Treatment Class
 *
 * Checks for:
 * - User profile fields used in SQL without sanitization
 * - Post meta/comment meta retrieved and used in queries
 * - Stored data from forms used in dynamic SQL
 * - Username/email fields used in WHERE clauses
 * - Custom fields concatenated into queries
 *
 * Second-order SQLi is harder to detect because the injection
 * happens in two stages: storage (safe) then retrieval + usage (unsafe).
 * This makes it particularly dangerous as standard input validation
 * won't catch it.
 *
 * @since 1.6093.1200
 */
class Treatment_Second_Order_SQL_Injection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'second-order-sql-injection';

	/**
	 * The treatment title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Second-Order SQL Injection';

	/**
	 * The treatment description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Detects second-order SQL injection where stored data is used unsafely';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Scans code for patterns where database-retrieved values
	 * are used directly in SQL queries without proper escaping.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Second_Order_SQL_Injection' );
	}
}
