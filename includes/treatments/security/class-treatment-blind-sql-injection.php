<?php
/**
 * Blind SQL Injection Treatment
 *
 * Detects potential blind SQL injection vulnerabilities in
 * themes, plugins, and custom code.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blind SQL Injection Treatment Class
 *
 * Checks for:
 * - Use of $wpdb->query() without $wpdb->prepare()
 * - Time-based SQL injection patterns (SLEEP, BENCHMARK)
 * - Boolean-based blind SQLi patterns
 * - Error suppression that could hide SQLi attempts
 * - User input concatenation in SQL queries
 *
 * Blind SQL injection is particularly dangerous because it
 * doesn't display errors, making it harder to detect. Attackers
 * can extract entire databases one character at a time using
 * time-based or boolean-based techniques.
 *
 * @since 0.6093.1200
 */
class Treatment_Blind_SQL_Injection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'blind-sql-injection';

	/**
	 * The treatment title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Blind SQL Injection Vulnerability';

	/**
	 * The treatment description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Detects potential blind SQL injection vulnerabilities in code';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Scans active theme and plugins for patterns indicating
	 * potential blind SQL injection vulnerabilities.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Blind_SQL_Injection' );
	}
}
