<?php
/**
 * Wordpress Database Charset Collation Diagnostic
 *
 * Wordpress Database Charset Collation issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1277.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Database Charset Collation Diagnostic Class
 *
 * @since 1.1277.0000
 */
class Diagnostic_WordpressDatabaseCharsetCollation extends Diagnostic_Base {

	protected static $slug = 'wordpress-database-charset-collation';
	protected static $title = 'Wordpress Database Charset Collation';
	protected static $description = 'Wordpress Database Charset Collation issue detected';
	protected static $family = 'functionality';

	public static function check() {
		global $wpdb;
		$issues = array();

		// Check 1: Database charset
		$db_charset = $wpdb->get_var( "SELECT @@character_set_database" );
		if ( $db_charset !== 'utf8mb4' && $db_charset !== 'utf8' ) {
			$issues[] = sprintf( __( 'Non-UTF charset (%s, encoding issues)', 'wpshadow' ), $db_charset );
		}

		// Check 2: Table charset consistency
		$table_charsets = $wpdb->get_results(
			"SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES
			 WHERE TABLE_SCHEMA = '{$wpdb->dbname}' AND TABLE_NAME LIKE '{$wpdb->prefix}%'",
			ARRAY_A
		);

		$charsets = array();
		foreach ( $table_charsets as $table ) {
			$charset = explode( '_', $table['TABLE_COLLATION'] )[0];
			$charsets[] = $charset;
		}

		$unique_charsets = array_unique( $charsets );
		if ( count( $unique_charsets ) > 1 ) {
			$issues[] = sprintf( __( 'Mixed charsets (%s, data corruption risk)', 'wpshadow' ), implode( ', ', $unique_charsets ) );
		}

		// Check 3: utf8mb4 support
		if ( $db_charset === 'utf8' ) {
			$issues[] = __( 'Using utf8 not utf8mb4 (emoji issues)', 'wpshadow' );
		}

		// Check 4: Collation
		$db_collation = $wpdb->get_var( "SELECT @@collation_database" );
		if ( strpos( $db_collation, '_ci' ) === false ) {
			$issues[] = sprintf( __( 'Case-sensitive collation (%s)', 'wpshadow' ), $db_collation );
		}

		// Check 5: wp-config.php settings
		if ( defined( 'DB_CHARSET' ) && DB_CHARSET !== 'utf8mb4' && DB_CHARSET !== 'utf8' ) {
			$issues[] = sprintf( __( 'wp-config charset mismatch (%s)', 'wpshadow' ), DB_CHARSET );
		}

		// Check 6: Binary collation
		if ( strpos( $db_collation, '_bin' ) !== false ) {
			$issues[] = __( 'Binary collation (search issues)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Database charset/collation has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wordpress-database-charset-collation',
		);
	}
}
