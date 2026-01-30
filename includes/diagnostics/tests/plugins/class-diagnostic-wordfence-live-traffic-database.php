<?php
/**
 * Wordfence Live Traffic Database Diagnostic
 *
 * Wordfence Live Traffic Database misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.840.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Live Traffic Database Diagnostic Class
 *
 * @since 1.840.0000
 */
class Diagnostic_WordfenceLiveTrafficDatabase extends Diagnostic_Base {

	protected static $slug = 'wordfence-live-traffic-database';
	protected static $title = 'Wordfence Live Traffic Database';
	protected static $description = 'Wordfence Live Traffic Database misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Live traffic enabled
		$live_traffic = wfConfig::get( 'liveTrafficEnabled', false );
		if ( ! $live_traffic ) {
			return null;
		}
		
		// Check 2: Traffic table size
		$table_name = $wpdb->prefix . 'wfHits';
		$table_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND((data_length + index_length) / 1024 / 1024, 2) 
				 FROM information_schema.tables 
				 WHERE table_schema = %s AND table_name = %s",
				DB_NAME,
				$table_name
			)
		);
		
		if ( $table_size > 500 ) {
			$issues[] = sprintf( __( 'Live traffic table: %.2f MB (cleanup recommended)', 'wpshadow' ), $table_size );
		}
		
		// Check 3: Record count
		$record_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		if ( $record_count > 100000 ) {
			$issues[] = sprintf( __( '%d traffic records (consider reducing retention)', 'wpshadow' ), $record_count );
		}
		
		// Check 4: Old records
		$old_records = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE ctime < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL %d DAY))",
				30
			)
		);
		
		if ( $old_records > 10000 ) {
			$issues[] = sprintf( __( '%d traffic records older than 30 days', 'wpshadow' ), $old_records );
		}
		
		// Check 5: Retention settings
		$max_rows = wfConfig::get( 'liveTraf_maxRows', 2000 );
		$max_age = wfConfig::get( 'liveTraf_maxAge', 2 );
		
		if ( $max_rows > 5000 || $max_age > 7 ) {
			$issues[] = sprintf( __( 'Retention: %d rows or %d days (high database load)', 'wpshadow' ), $max_rows, $max_age );
		}
		
		// Check 6: Database indexes
		$indexes = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW INDEX FROM {$table_name} WHERE Key_name != %s",
				'PRIMARY'
			)
		);
		
		if ( count( $indexes ) < 2 && $record_count > 50000 ) {
			$issues[] = __( 'Missing indexes on traffic table (slow queries)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 82;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 76;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of database issues */
				__( 'Wordfence live traffic database has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wordfence-live-traffic-database',
		);
	}
}
