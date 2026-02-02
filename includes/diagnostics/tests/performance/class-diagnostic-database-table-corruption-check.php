<?php
/**
 * Database Table Corruption Check Diagnostic
 *
 * Verifies database table integrity.
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
 * Runs CHECK TABLE on WordPress tables to identify corruption.
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
