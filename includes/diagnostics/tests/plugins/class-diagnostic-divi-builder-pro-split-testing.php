<?php
/**
 * Divi Builder Pro Split Testing Diagnostic
 *
 * Divi Builder Pro Split Testing issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.810.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Pro Split Testing Diagnostic Class
 *
 * @since 1.810.0000
 */
class Diagnostic_DiviBuilderProSplitTesting extends Diagnostic_Base {

	protected static $slug = 'divi-builder-pro-split-testing';
	protected static $title = 'Divi Builder Pro Split Testing';
	protected static $description = 'Divi Builder Pro Split Testing issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'et_setup_theme' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check for split test post type
		$split_tests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'et_pb_ab_test'
			)
		);

		if ( $split_tests === 0 ) {
			$issues[] = 'no_split_tests_configured';
			$threat_level += 20;
		} else {
			// Check for active tests
			$active_tests = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} 
					 WHERE post_type = %s AND post_status = %s",
					'et_pb_ab_test',
					'publish'
				)
			);
			if ( $active_tests === 0 ) {
				$issues[] = 'no_active_split_tests';
				$threat_level += 15;
			}
		}

		// Check split test settings
		$divi_options = get_option( 'et_divi', array() );
		$ab_testing_enabled = isset( $divi_options['divi_ab_testing'] ) ? $divi_options['divi_ab_testing'] : false;
		if ( ! $ab_testing_enabled ) {
			$issues[] = 'ab_testing_disabled';
			$threat_level += 25;
		}

		// Check for test data collection
		$ab_stats_table = $wpdb->prefix . 'et_ab_stats';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$ab_stats_table}'" ) === $ab_stats_table;
		if ( ! $table_exists && $split_tests > 0 ) {
			$issues[] = 'stats_table_missing';
			$threat_level += 20;
		}

		// Check conversion tracking
		$track_conversions = isset( $divi_options['divi_ab_track_conversions'] ) ? $divi_options['divi_ab_track_conversions'] : false;
		if ( ! $track_conversions && $split_tests > 0 ) {
			$issues[] = 'conversion_tracking_disabled';
			$threat_level += 20;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of split testing issues */
				__( 'Divi Builder split testing has issues: %s. This prevents A/B testing optimization and conversion tracking.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/divi-builder-pro-split-testing',
			);
		}
		
		return null;
	}
}
