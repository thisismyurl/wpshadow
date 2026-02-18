<?php
/**
 * Plugin Database Corruption Diagnostic
 *
 * Checks for database table integrity issues in plugin-created tables.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1331
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Database Corruption Diagnostic Class
 *
 * Scans plugin tables for integrity issues using CHECK TABLE.
 *
 * @since 1.5049.1331
 */
class Diagnostic_Plugin_Database_Corruption extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-database-corruption';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Database Corruption';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks plugin database tables for integrity issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1331
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$core_tables = array(
			'posts',
			'postmeta',
			'users',
			'usermeta',
			'options',
			'links',
			'comments',
			'commentmeta',
			'terms',
			'term_relationships',
			'term_taxonomy',
			'termmeta',
		);

		$tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );
		$plugin_tables = array();

		foreach ( $tables as $table ) {
			$table_name = str_replace( $wpdb->prefix, '', $table );
			if ( in_array( $table_name, $core_tables, true ) ) {
				continue;
			}
			$plugin_tables[] = $table;
		}

		if ( empty( $plugin_tables ) ) {
			return null;
		}

		// Limit the scan to 20 tables for performance.
		$plugin_tables = array_slice( $plugin_tables, 0, 20 );
		$corrupt_tables = array();

		foreach ( $plugin_tables as $table ) {
			$results = $wpdb->get_results( "CHECK TABLE {$table}", ARRAY_A );
			if ( empty( $results ) ) {
				continue;
			}

			foreach ( $results as $row ) {
				if ( isset( $row['Msg_type'], $row['Msg_text'] ) && 'OK' !== strtoupper( $row['Msg_text'] ) ) {
					$corrupt_tables[] = array(
						'table'    => $table,
						'message'  => $row['Msg_text'],
						'msg_type' => $row['Msg_type'],
					);
				}
			}
		}

		if ( ! empty( $corrupt_tables ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Plugin database tables may be corrupted or need repair', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'      => array(
					'checked_tables' => count( $plugin_tables ),
					'corrupt_tables' => array_slice( $corrupt_tables, 0, 10 ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-database-corruption',
			);
		}

		return null;
	}
}
