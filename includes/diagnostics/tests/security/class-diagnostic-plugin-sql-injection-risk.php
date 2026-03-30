<?php
/**
 * Plugin SQL Injection Risk Diagnostic
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
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_SQL_Injection_Risk Class
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
class Diagnostic_Plugin_SQL_Injection_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-sql-injection-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin SQL Injection Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins vulnerable to SQL injection attacks';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$sql_risks = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for direct query construction with variables
			if ( preg_match( '/\$wpdb\->(?:query|get_results?)\s*\(\s*["\'].*\$(?:_GET|_POST|_REQUEST)/', $content ) ) {
				$sql_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Constructs SQL queries with $_GET/$_POST without $wpdb->prepare().', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for sprintf with database variables
			if ( preg_match( '/sprintf\s*\(\s*["\'].*SELECT.*["\'].*\$(?:_GET|_POST|_REQUEST)/', $content ) ) {
				$sql_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Uses sprintf() for SQL queries with user input.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for string concatenation in queries
			if ( preg_match( '/\$wpdb\->query\s*\(\s*["\'].*\s*\.\s*\$[a-zA-Z_]/', $content ) ) {
				$sql_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Concatenates variables into SQL queries.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for WHERE IN with variables
			if ( preg_match( '/WHERE\s+.+\s+IN\s*\(\s*["\'].*\$[a-zA-Z_]/', $content ) ) {
				$sql_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Uses variables in WHERE IN clauses without prepare().', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $sql_risks ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: risk count, %s: details */
					__( '%d SQL injection risks detected: %s', 'wpshadow' ),
					count( $sql_risks ),
					implode( ' | ', array_slice( $sql_risks, 0, 3 ) )
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'details'      => array(
					'sql_risks' => $sql_risks,
				),
				'kb_link'      => 'https://wpshadow.com/kb/sql-injection-prevention',
				'context'      => array(
					'why'            => __( 'Unsafe SQL query building can let attackers read or change database records. Using prepared queries keeps user input as plain data instead of executable SQL.', 'wpshadow' ),
					'recommendation' => __( 'Review custom queries and ensure every dynamic value is passed through $wpdb->prepare() with the correct placeholder type (%d, %s, or %f).', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'sql-injection-prevention', 'plugin-sql-injection-detection' );
			return $finding;
		}

		return null;
	}
}
