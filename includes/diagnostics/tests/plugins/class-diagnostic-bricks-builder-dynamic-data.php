<?php
/**
 * Bricks Builder Dynamic Data Diagnostic
 *
 * Bricks Builder Dynamic Data issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.818.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bricks Builder Dynamic Data Diagnostic Class
 *
 * @since 1.818.0000
 */
class Diagnostic_BricksBuilderDynamicData extends Diagnostic_Base {

	protected static $slug = 'bricks-builder-dynamic-data';
	protected static $title = 'Bricks Builder Dynamic Data';
	protected static $description = 'Bricks Builder Dynamic Data issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'BRICKS_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Dynamic data query caching
		$query_cache = get_option( 'bricks_query_cache_enabled', false );
		if ( ! $query_cache ) {
			$issues[] = __( 'Dynamic data query caching disabled (performance impact)', 'wpshadow' );
		}
		
		// Check 2: Custom query loops count
		$custom_queries = get_option( 'bricks_custom_queries', array() );
		$query_count = is_array( $custom_queries ) ? count( $custom_queries ) : 0;
		
		if ( $query_count > 50 ) {
			$issues[] = sprintf( __( '%d custom query loops (review complexity)', 'wpshadow' ), $query_count );
		}
		
		// Check 3: ACF integration
		if ( class_exists( 'ACF' ) ) {
			$acf_optimization = get_option( 'bricks_acf_optimization', false );
			if ( ! $acf_optimization ) {
				$issues[] = __( 'ACF field optimization not enabled (query overhead)', 'wpshadow' );
			}
		}
		
		// Check 4: Query results per page
		$max_results = get_option( 'bricks_query_max_results', 10 );
		if ( $max_results > 100 ) {
			$issues[] = sprintf( __( 'Max %d query results per page (memory intensive)', 'wpshadow' ), $max_results );
		}
		
		// Check 5: Dynamic data in header/footer
		$header_queries = get_option( 'bricks_header_dynamic_data', 0 );
		if ( $header_queries > 3 ) {
			$issues[] = sprintf( __( '%d queries in header (every page load)', 'wpshadow' ), $header_queries );
		}
		
		
		// Check 6: Feature initialization
		if ( ! (get_option( "features_init" ) !== false) ) {
			$issues[] = __( 'Feature initialization', 'wpshadow' );
		}

		// Check 7: Database tables
		if ( ! (! empty( $GLOBALS["wpdb"] )) ) {
			$issues[] = __( 'Database tables', 'wpshadow' );
		}

		// Check 8: Hook registration
		if ( ! (has_action( "init" )) ) {
			$issues[] = __( 'Hook registration', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of dynamic data issues */
				__( 'Bricks Builder dynamic data has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/bricks-builder-dynamic-data',
		);
	}
}
