<?php
/**
 * Plugin SQL Injection Risk Treatment
 *
 * Detects plugins vulnerable to SQL injection attacks. Plugin includes user input
 * directly in SQL query (no prepared statements). Attacker modifies input.
 * SQL query modified. Attacker extracts entire database.
 *
 * **What This Check Does:**
 * - Scans plugin files for SQL query construction
 * - Checks if $wpdb->prepare() used
 * - Detects direct string concatenation in SQL
 * - Tests for parameterized queries
 * - Validates if user input escaped
 * - Returns severity if SQL injection vulnerable
 *
 * **Why This Matters:**
 * SQL injection = database access. Scenarios:
 * - Plugin constructs SQL with user input: "SELECT * FROM users WHERE id=" . $_GET['id']
 * - Attacker passes: id=1 OR 1=1
 * - Query becomes: SELECT * FROM users WHERE id=1 OR 1=1
 * - Returns ALL users (not just one)
 * - Attacker extracts all user data (including passwords)
 *
 * **Business Impact:**
 * Search plugin builds query: "SELECT * FROM posts WHERE title LIKE '" . $_GET['search'] . "'".
 * Attacker searches: ' OR '1'='1
 * Query becomes: SELECT * FROM posts WHERE title LIKE '' OR '1'='1'
 * Returns entire database. Attacker downloads all data. GDPR fine: $2M+.
 * Proper approach: $wpdb->prepare() = parameterized query (injection impossible).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Database protected from injection
 * - #9 Show Value: Prevents database extraction
 * - #10 Beyond Pure: Query parameterization
 *
 * **Related Checks:**
 * - Database Security Configuration (DB access control)
 * - Input Sanitization Audit (validation)
 * - Plugin Code Injection Prevention (related vector)
 *
 * **Learn More:**
 * SQL injection: https://wpshadow.com/kb/wordpress-sql-injection
 * Video: Preventing SQL injection (12min): https://wpshadow.com/training/sql-injection
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Plugin_SQL_Injection_Risk Class
 *
 * Identifies plugins vulnerable to SQL injection.
 *
 * **Detection Pattern:**
 * 1. Scan plugin files for $wpdb->query() calls
 * 2. Check if SQL constructed with user input
 * 3. Test if $wpdb->prepare() used (safe)
 * 4. Detect direct concatenation (unsafe)
 * 5. Test actual SQL injection payload
 * 6. Return severity if injection confirmed
 *
 * **Real-World Scenario:**
 * Product filter plugin builds query: "SELECT * FROM products WHERE id=" . $_GET['id'].
 * Attacker passes: id=999 UNION SELECT user_login, user_pass FROM wp_users.
 * Gets all admin passwords. With $wpdb->prepare(): injection impossible (user
 * input treated as literal value, not SQL code).
 *
 * **Implementation Notes:**
 * - Scans plugin files for SQL construction
 * - Tests for parameterized queries (prepare)
 * - Attempts actual injection payloads
 * - Severity: critical (injection confirmed), high (no prepare used)
 * - Treatment: use $wpdb->prepare() for all queries
 *
 * @since 1.6093.1200
 */
class Treatment_Plugin_SQL_Injection_Risk extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-sql-injection-risk';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin SQL Injection Risk';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins vulnerable to SQL injection attacks';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_SQL_Injection_Risk' );
	}
}
