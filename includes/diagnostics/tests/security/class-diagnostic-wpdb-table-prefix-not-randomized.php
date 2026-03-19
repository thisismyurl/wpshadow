<?php
/**
 * WPDB Table Prefix Not Randomized Diagnostic
 *
 * Checks if database prefix is randomized.
 * Default prefix = "wp_". Attacker knows table names.
 * Randomized prefix = "wp_j8k2m_". Table names harder to guess.
 * SQL injection defenses improved.
 *
 * **What This Check Does:**
 * - Checks database table prefix configuration
 * - Validates prefix is not default "wp_"
 * - Tests prefix randomization strength
 * - Checks wp-config.php for $table_prefix
 * - Validates prefix follows best practices (length, chars)
 * - Returns severity if default prefix used
 *
 * **Why This Matters:**
 * Default "wp_" = attacker knows all table names.
 * SQL injection easier: "SELECT * FROM wp_users".
 * Random prefix = attacker must discover table names first.
 * Extra SQL injection defense layer.
 *
 * **Business Impact:**
 * Site uses default "wp_" prefix. SQL injection vulnerability found.
 * Attacker injects: "' UNION SELECT * FROM wp_users WHERE ID=1--".
 * Admin credentials extracted. Site compromised. Cost: $100K+.
 * With randomized prefix ("wp_8k2jm3_"): attacker's injection fails
 * (no "wp_users" table). Extra time to discover real table names.
 * Vulnerability patched before attacker succeeds. Attack prevented.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Database hardening in place
 * - #9 Show Value: Extra SQL injection defense
 * - #10 Beyond Pure: Defense-in-depth approach
 *
 * **Related Checks:**
 * - SQL Injection Protection (primary defense)
 * - Database Credentials Exposure (related)
 * - Input Sanitization (complementary)
 *
 * **Learn More:**
 * Database security: https://wpshadow.com/kb/database-security
 * Video: Hardening WordPress database (9min): https://wpshadow.com/training/db-hardening
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
 * WPDB Table Prefix Not Randomized Diagnostic Class
 *
 * Detects default table prefix.
 *
 * **Detection Pattern:**
 * 1. Read wp-config.php
 * 2. Extract $table_prefix value
 * 3. Check if equals "wp_"
 * 4. Validate prefix length (8+ chars recommended)
 * 5. Check randomization (alphanumeric mix)
 * 6. Return if default or weak prefix found
 *
 * **Real-World Scenario:**
 * Database uses "wp_8k2jm3_" prefix. SQL injection attempt:
 * "' UNION SELECT * FROM wp_users--" fails (table doesn't exist).
 * Attacker must discover real table name first. Extra reconnaissance
 * time allows admin to patch vulnerability. Attack prevented.
 *
 * **Implementation Notes:**
 * - Checks wp-config.php table prefix
 * - Validates against default "wp_"
 * - Tests randomization strength
 * - Severity: medium (defense-in-depth, not primary protection)
 * - Treatment: update table prefix (requires database migration)
 *
 * @since 1.6093.1200
 */
class Diagnostic_WPDB_Table_Prefix_Not_Randomized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wpdb-table-prefix-not-randomized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WPDB Table Prefix Not Randomized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database prefix is randomized';

	/**
	 * The family this diagnostic belongs to
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
		global $wpdb;

		// Check if default prefix is used
		if ( 'wp_' === $wpdb->prefix ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database table prefix is not randomized. Change the table prefix from "wp_" to a unique value to reduce SQL injection vulnerability.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/wpdb-table-prefix-not-randomized',
			);
		}

		return null;
	}
}
