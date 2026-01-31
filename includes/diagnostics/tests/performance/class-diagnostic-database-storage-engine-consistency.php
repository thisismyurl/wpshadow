<?php
/**
 * Database Storage Engine Consistency Diagnostic
 *
 * Checks that all database tables use the same storage engine.
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
 * Database Storage Engine Consistency Diagnostic Class
 *
 * Detects tables using different storage engines.
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
