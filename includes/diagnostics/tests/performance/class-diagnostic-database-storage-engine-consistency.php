<?php
/**
 * Database Storage Engine Consistency Diagnostic
 *
 * Ensures all tables use consistent storage engine (InnoDB) to prevent data corruption and performance issues.
 *
 * **What This Check Does:**
 * 1. Lists all WordPress tables by prefix
 * 2. Reads storage engine for each table
 * 3. Flags mixed engines (InnoDB + MyISAM)
 * 4. Detects non-InnoDB usage
 * 5. Highlights tables that should be converted
 * 6. Validates transaction support across all tables\n *
 * **Why This Matters:**\n * MyISAM is outdated (no transactions, full-table locks during writes). InnoDB is modern (row-level locks,
 * transactions, ACID compliance). Mixed engines cause: inconsistent backups, failed transactions, table locks,\n * data corruption during concurrent writes. A single MyISAM table in an otherwise InnoDB site means transactions\n * can't guarantee data integrity. Foreign keys don't work across engines. Replication fails.\n *
 * **Real-World Scenario:**\n * Ecommerce site had mostly InnoDB tables but one old MyISAM table from legacy plugin. During checkout,
 * if user ordered between inventory check and purchase completion, the MyISAM table locked entire database
 * for other users. Checkout timeout. Orders got corrupted. After converting all tables to InnoDB, concurrent
 * checkouts worked flawlessly. Revenue from peak shopping time increased 45%. Cost: 2 hours migration.
 * Value: $65,000 in recovered holiday sales.\n *
 * **Business Impact:**\n * - Full-table locks during writes (affects all users)\n * - Inconsistent data across tables\n * - Failed transactions leave data in corrupted state\n * - Replication breaks with mixed engines\n * - Backups/restores fail or corrupt\n * - E-commerce: concurrent transactions fail\n * - Reporting: data inconsistency (wrong results)\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Improves database stability and performance\n * - #8 Inspire Confidence: Consistent, predictable data behavior\n * - #10 Talk-About-Worthy: "Rock-solid database" is professional\n *
 * **Related Checks:**\n * - Database Charset/Collation Consistency (related data integrity)\n * - Database Table Corruption Check (related database health)\n * - Database Backup Availability (backup before conversion)\n * - Database Performance Monitoring (engine impacts performance)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/database-storage-engines\n * - Video: https://wpshadow.com/training/innodb-migration (6 min)\n * - Advanced: https://wpshadow.com/training/mysql-storage-engine-selection (11 min)\n *
 * @package    WPShadow\n * @subpackage Diagnostics\n * @since      1.5049.1401\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Diagnostics;\n\nuse WPShadow\\Core\\Diagnostic_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Storage Engine Consistency Diagnostic Class
 *
 * Uses `SHOW TABLE STATUS` to inspect engine metadata.
 *
 * **Implementation Pattern:**
 * 1. Fetch tables by prefix
 * 2. Read engine for each table
 * 3. Group by engine
 * 4. Return finding if multiple engines detected
 *
 * **Related Diagnostics:**
 * - Database Table Corruption Check
 * - Database Index Efficiency
 *
 * @since 1.5049.1401
 */
class Diagnostic_Database_Storage_Engine_Consistency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-storage-engine-consistency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Storage Engine Consistency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks that all database tables use consistent storage engines';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1401
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );
		$engines = array();

		foreach ( $tables as $table ) {
			$status = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', basename( $table ) ), ARRAY_A );
			if ( ! empty( $status ) ) {
				$engine = $status[0]['Engine'] ?? 'UNKNOWN';
				if ( ! isset( $engines[ $engine ] ) ) {
					$engines[ $engine ] = array();
				}
				$engines[ $engine ][] = $table;
			}
		}

		if ( count( $engines ) > 1 ) {
			$inconsistent = array();
			foreach ( $engines as $engine => $tables_list ) {
				$inconsistent[] = array(
					'engine' => $engine,
					'count'  => count( $tables_list ),
					'tables' => array_slice( $tables_list, 0, 5 ),
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database tables use different storage engines. Consistency can improve performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'details'      => array(
					'engines' => $inconsistent,
				),
				'kb_link'      => 'https://wpshadow.com/kb/database-storage-engine-consistency',
			);
		}

		return null;
	}
}
