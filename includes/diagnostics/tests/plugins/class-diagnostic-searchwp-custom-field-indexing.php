<?php
/**
 * SearchWP Custom Field Indexing Diagnostic
 *
 * SearchWP custom fields slowing index.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.408.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SearchWP Custom Field Indexing Diagnostic Class
 *
 * @since 1.408.0000
 */
class Diagnostic_SearchwpCustomFieldIndexing extends Diagnostic_Base {

	protected static $slug = 'searchwp-custom-field-indexing';
	protected static $title = 'SearchWP Custom Field Indexing';
	protected static $description = 'SearchWP custom fields slowing index';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'SearchWP' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Custom fields indexed
		$indexed_fields = get_option( 'searchwp_indexed_custom_fields', array() );
		
		if ( count( $indexed_fields ) > 50 ) {
			$issues[] = sprintf( __( '%d custom fields indexed (slow indexing)', 'wpshadow' ), count( $indexed_fields ) );
		}
		
		// Check 2: Indexing frequency
		$index_frequency = get_option( 'searchwp_index_frequency', 'hourly' );
		if ( 'realtime' === $index_frequency ) {
			$issues[] = __( 'Real-time indexing (high CPU usage)', 'wpshadow' );
		}
		
		// Check 3: Field weight configuration
		$field_weights = get_option( 'searchwp_field_weights', array() );
		if ( empty( $field_weights ) ) {
			$issues[] = __( 'No field weights (relevance issues)', 'wpshadow' );
		}
		
		// Check 4: Excluded fields
		$excluded_fields = get_option( 'searchwp_excluded_fields', array() );
		if ( empty( $excluded_fields ) ) {
			$issues[] = __( 'No excluded fields (indexing noise)', 'wpshadow' );
		}
		
		// Check 5: Index size
		global $wpdb;
		$index_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2)
				FROM information_schema.TABLES
				WHERE table_schema = %s AND table_name LIKE %s",
				DB_NAME,
				$wpdb->prefix . 'swp_%'
			)
		);
		
		if ( $index_size > 500 ) {
			$issues[] = sprintf( __( '%s MB index size (database overhead)', 'wpshadow' ), $index_size );
		}
		
		// Check 6: Throttle indexing
		$throttle = get_option( 'searchwp_throttle_indexing', 'yes' );
		if ( 'no' === $throttle ) {
			$issues[] = __( 'No throttling (server overload risk)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 58;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 52;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of custom field indexing issues */
				__( 'SearchWP has %d custom field indexing issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/searchwp-custom-field-indexing',
		);
	}
}
