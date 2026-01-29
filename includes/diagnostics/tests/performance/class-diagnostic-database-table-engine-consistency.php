<?php
/**
 * Database Table Engine Consistency Diagnostic
 *
 * Ensures all tables use InnoDB storage engine, not legacy MyISAM.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Table Engine Consistency Class
 *
 * Tests table engines.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Database_Table_Engine_Consistency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-table-engine-consistency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Table Engine Consistency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures all tables use InnoDB storage engine, not legacy MyISAM';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$engine_check = self::check_table_engines();
		
		if ( $engine_check['myisam_tables'] > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of MyISAM tables */
					__( 'Found %d tables using legacy MyISAM engine (no transactions, crash recovery, or foreign keys)', 'wpshadow' ),
					$engine_check['myisam_tables']
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-table-engine-consistency',
				'meta'         => array(
					'total_tables'       => $engine_check['total_tables'],
					'myisam_tables'      => $engine_check['myisam_tables'],
					'innodb_tables'      => $engine_check['innodb_tables'],
					'myisam_table_names' => $engine_check['myisam_table_names'],
				),
			);
		}

		return null;
	}

	/**
	 * Check table engines.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_table_engines() {
		global $wpdb;

		$check = array(
			'total_tables'       => 0,
			'myisam_tables'      => 0,
			'innodb_tables'      => 0,
			'myisam_table_names' => array(),
		);

		// Get all tables.
		$tables = $wpdb->get_results( "SHOW TABLE STATUS" );
		
		foreach ( $tables as $table ) {
			++$check['total_tables'];

			if ( isset( $table->Engine ) ) {
				if ( 'MyISAM' === $table->Engine ) {
					++$check['myisam_tables'];
					$check['myisam_table_names'][] = $table->Name;
				} elseif ( 'InnoDB' === $table->Engine ) {
					++$check['innodb_tables'];
				}
			}
		}

		return $check;
	}
}
