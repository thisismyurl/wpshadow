<?php
/**
 * SQL Injection Protection Treatment
 *
 * Issue #4883: Custom Queries Not Using Prepared Statements
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if database queries use prepared statements.
 * SQL injection is still the #1 web vulnerability (OWASP).
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
 * Treatment_SQL_Injection_Protection Class
 *
 * Checks for:
 * - All queries use $wpdb->prepare()
 * - No string concatenation in SQL queries
 * - No direct use of $_POST/$_GET in queries
 * - Table/column names sanitized (limited character set)
 * - Integer parameters cast to (int)
 * - No dynamic query construction without preparation
 * - WordPress query APIs used (WP_Query, get_posts)
 *
 * Why this matters:
 * - SQL injection is #1 OWASP vulnerability
 * - Attackers can read entire database
 * - Attackers can delete all data
 * - One vulnerable query compromises entire site
 *
 * @since 1.6093.1200
 */
class Treatment_SQL_Injection_Protection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'sql-injection-protection';

	/**
	 * The treatment title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Custom Queries Not Using Prepared Statements';

	/**
	 * The treatment description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if database queries use prepared statements to prevent SQL injection';

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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SQL_Injection_Protection' );
	}
}
