<?php
/**
 * Rank Math Analytics Performance Diagnostic
 *
 * Rank Math Analytics Performance configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.696.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rank Math Analytics Performance Diagnostic Class
 *
 * @since 1.696.0000
 */
class Diagnostic_RankMathAnalyticsPerformance extends Diagnostic_Base {

	protected static $slug = 'rank-math-analytics-performance';
	protected static $title = 'Rank Math Analytics Performance';
	protected static $description = 'Rank Math Analytics Performance configuration issues';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'RANK_MATH_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check if analytics module is enabled
		$analytics_enabled = get_option( 'rank_math_modules', array() );
		if ( ! in_array( 'analytics', $analytics_enabled, true ) ) {
			return null; // Analytics not enabled
		}

		// Check analytics database table size
		$table_name = $wpdb->prefix . 'rank_math_analytics_objects';
		$table_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb
				 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
				DB_NAME,
				$table_name
			)
		);
		if ( $table_size > 100 ) {
			$issues[] = 'analytics_table_too_large';
			$threat_level += 20;
		}

		// Check data pruning configuration
		$data_retention = get_option( 'rank_math_analytics_data_retention', 90 );
		if ( $data_retention > 365 ) {
			$issues[] = 'data_retention_too_long';
			$threat_level += 15;
		}

		// Check Google Search Console connection
		$gsc_connected = get_option( 'rank_math_google_analytic_profile', '' );
		if ( empty( $gsc_connected ) ) {
			$issues[] = 'google_search_console_not_connected';
			$threat_level += 10;
		}

		// Check for old data not being pruned
		$old_data = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name}
				 WHERE created < %s",
				date( 'Y-m-d H:i:s', strtotime( '-1 year' ) )
			)
		);
		if ( $old_data > 10000 ) {
			$issues[] = 'old_data_not_pruned';
			$threat_level += 15;
		}

		// Check analytics refresh frequency
		$last_sync = get_option( 'rank_math_analytics_last_sync', 0 );
		if ( $last_sync && ( time() - $last_sync ) > 604800 ) {
			$issues[] = 'analytics_not_syncing';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of analytics performance issues */
				__( 'Rank Math analytics has performance problems: %s. This can slow down your database and admin dashboard significantly.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/rank-math-analytics-performance',
			);
		}
		
		return null;
	}
}
