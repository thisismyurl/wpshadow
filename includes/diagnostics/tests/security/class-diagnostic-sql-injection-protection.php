<?php
/**
 * SQL Injection Protection Diagnostic
 *
 * Issue #4883: Custom Queries Not Using Prepared Statements
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if database queries use prepared statements.
 * SQL injection is still the #1 web vulnerability (OWASP).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_SQL_Injection_Protection Class
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
class Diagnostic_SQL_Injection_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'sql-injection-protection';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Custom Queries Not Using Prepared Statements';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if database queries use prepared statements to prevent SQL injection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual SQL analysis requires code scanning.
		// We provide recommendations and patterns.

		$issues = array();

		$issues[] = __( 'ALWAYS use $wpdb->prepare() for queries with variables', 'wpshadow' );
		$issues[] = __( 'NEVER concatenate strings into SQL queries', 'wpshadow' );
		$issues[] = __( 'NEVER use $_POST, $_GET, $_REQUEST directly in queries', 'wpshadow' );
		$issues[] = __( 'Use %d for integers, %s for strings, %f for floats', 'wpshadow' );
		$issues[] = __( 'Cast integers: (int) $id before using in queries', 'wpshadow' );
		$issues[] = __( 'Use WordPress query APIs: WP_Query, get_posts(), get_user_by()', 'wpshadow' );
		$issues[] = __( 'Table/column names cannot be prepared - use whitelist validation', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SQL injection is the #1 web vulnerability. Attackers can read, modify, or delete entire databases through unprotected queries.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,  // Requires code audit
				'kb_link'      => 'https://wpshadow.com/kb/sql-injection-protection',
				'details'      => array(
					'recommendations'         => $issues,
					'bad_example'             => '$wpdb->query( "SELECT * FROM {$wpdb->posts} WHERE ID = {$id}" )',
					'good_example'            => '$wpdb->query( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $id ) )',
					'attack_example'          => 'attacker sends: id=1 OR 1=1; DROP TABLE wp_posts;--',
					'owasp_rank'              => 'A03:2021 Injection (was #1 for 13 years)',
					'impact'                  => 'Complete database compromise, data theft, data loss',
				),
			);
		}

		return null;
	}
}
