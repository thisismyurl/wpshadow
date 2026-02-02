<?php
/**
 * Database Table Corruption Check Diagnostic
 *
 * Verifies database table integrity by running integrity checks on WordPress
 * tables. Corruption can cause missing posts, broken admin screens, or failed
 * updates. This diagnostic detects early warning signs so you can repair tables
 * before data loss escalates.
 *
 * **What This Check Does:**
 * - Runs `CHECK TABLE` on WordPress core tables
 * - Detects warnings or errors returned by the database engine
 * - Reports which tables are affected and why
 * - Enables automatic repair via the associated treatment
 *
 * **Why This Matters:**
 * Table corruption can occur after abrupt shutdowns, disk issues, or failed
 * migrations. Corrupted tables often manifest as white screens, missing data,
 * or errors like “Table is marked as crashed.”
 *
 * **Real-World Failure Scenario:**
 * - Hosting server crashes during update
 * - `wp_posts` table becomes corrupted
 * - Admin can’t edit posts; front-end pages 500
 *
 * Result: Site downtime and potential data loss.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Early detection prevents catastrophic failures
 * - #9 Show Value: Protects uptime and data integrity
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/database-table-corruption
 * or https://wpshadow.com/training/database-maintenance
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1401
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Table Corruption Check Diagnostic Class
 *
 * Uses `CHECK TABLE` to validate table health and detect corruption signals.
 *
 * **Implementation Pattern:**
 * 1. Enumerate WordPress tables with `$wpdb->prefix`
 * 2. Run `CHECK TABLE` for each table
 * 3. Collect warnings/errors
 * 4. Return findings with affected table list
 *
 * **Related Diagnostics:**
 * - Database Storage Engine Consistency
 * - Database Index Efficiency
 * - Plugin Database Corruption
 *
 * @since 1.5049.1401
 */
class Diagnostic_Database_Table_Corruption_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-table-corruption-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Table Corruption Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks WordPress database tables for corruption';

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

		$core_tables = array(
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->users,
			$wpdb->usermeta,
			$wpdb->comments,
			$wpdb->commentmeta,
			$wpdb->options,
			$wpdb->terms,
			$wpdb->termmeta,
		);

		$corrupt = array();

		foreach ( $core_tables as $table ) {
			$results = $wpdb->get_results( "CHECK TABLE {$table}", ARRAY_A );
			if ( empty( $results ) ) {
				continue;
			}

			foreach ( $results as $row ) {
				if ( isset( $row['Msg_text'], $row['Msg_type'] ) && 'OK' !== strtoupper( $row['Msg_text'] ) ) {
					$corrupt[] = array(
						'table'    => $table,
						'status'   => $row['Msg_type'],
						'message'  => $row['Msg_text'],
					);
				}
			}
		}

		if ( ! empty( $corrupt ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of corrupt tables */
					_n(
						'%d database table has corruption and needs repair',
						'%d database tables have corruption and need repair',
						count( $corrupt ),
						'wpshadow'
					),
					count( $corrupt )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => true,
				'details'      => array(
					'corrupt_tables' => $corrupt,
					'tables_affected' => array_column( $corrupt, 'table' ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/database-table-corruption-check',
			);
		}

		return null;
	}
}
