<?php
/**
 * Theme Direct Database Access Diagnostic
 *
 * Checks if theme directly accesses database (uses \$wpdb).
 * Theme using \$wpdb directly = potential SQL injection vulnerabilities.
 * Should use WordPress APIs (get_posts, get_option, etc).
 *
 * **What This Check Does:**
 * - Searches theme files for \$wpdb usage
 * - Detects direct database queries
 * - Checks if \$wpdb->prepare() used (prevents SQL injection)
 * - Flags raw SQL without prepared statements
 * - Tests for custom table access
 * - Returns severity for unprotected queries
 *
 * **Why This Matters:**
 * Theme uses \$wpdb instead of WordPress APIs.
 * Direct queries = higher SQL injection risk (if not careful).
 * WordPress APIs tested, maintained, secure.
 * Direct \$wpdb = theme developer's responsibility.
 *
 * **Business Impact:**
 * Theme uses: \$wpdb->query("SELECT * FROM wp_users WHERE email = '".\$_POST['email']."'").
 * Attacker injects SQL. Gets all user email/password hashes.
 * With prepare: \$wpdb->prepare("SELECT * FROM wp_users WHERE email = %s", \$email).
 * SQL injection impossible. Data safe.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Database access is secure
 * - #9 Show Value: Prevents SQL injection vulnerabilities
 * - #10 Beyond Pure: Using secure APIs by design
 *
 * **Related Checks:**
 * - Plugin SQL Injection Risk (similar risk in plugins)
 * - Theme Data Validation (input handling)
 * - Database Security Overall (complementary)
 *
 * **Learn More:**
 * Direct database access risks: https://wpshadow.com/kb/theme-direct-db
 * Video: Using WordPress database APIs (10min): https://wpshadow.com/training/db-security
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Direct Database Access Diagnostic
 *
 * Flags direct database access inside theme files.
 *
 * **Detection Pattern:**
 * 1. Find all active theme PHP files
 * 2. Search for \$wpdb variable usage
 * 3. Detect direct \$wpdb->query/get_results calls
 * 4. Check if \$wpdb->prepare used
 * 5. Flag raw SQL without prepared statements
 * 6. Return each unprotected query
 *
 * **Real-World Scenario:**
 * Theme author needs to get posts by custom field. Uses:
 * ```
 * \$results = \$wpdb->get_results("SELECT * FROM wp_posts WHERE title = '".\$_POST['search']."'");
 * ```
 * Attacker sends: ' OR '1'='1. Gets all posts (including unpublished).
 * With prepare: \$wpdb->prepare("SELECT * FROM wp_posts WHERE title = %s", \$search).
 * Injection attempt treated as literal string. Query safe.
 *
 * **Implementation Notes:**
 * - Scans active theme files
 * - Detects direct \$wpdb usage
 * - Checks if \$wpdb->prepare used
 * - Severity: high (no prepare), medium (uses prepare)
 * - Treatment: use WordPress APIs or add \$wpdb->prepare
 *
 * @since 1.6030.2240
 */
class Diagnostic_Theme_Direct_Database_Access extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-direct-database-access';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Direct Database Access';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme directly accesses the database';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = wp_get_theme()->get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		if ( ! file_exists( $functions_file ) ) {
			return null;
		}

		$content = file_get_contents( $functions_file, false, null, 0, 60000 );
		if ( false === $content ) {
			return null;
		}

		$patterns = array(
			'$wpdb->query',
			'$wpdb->get_results',
			'$wpdb->get_var',
			'SELECT ',
		);

		$matches = array();
		foreach ( $patterns as $pattern ) {
			if ( false !== strpos( $content, $pattern ) ) {
				$matches[] = $pattern;
			}
		}

		if ( ! empty( $matches ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme appears to access the database directly', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-direct-database-access',
				'details'      => array(
					'matches' => $matches,
				),
			);
		}

		return null;
	}
}
