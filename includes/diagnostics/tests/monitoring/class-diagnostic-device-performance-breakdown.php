<?php
/**
 * Device Performance Breakdown Diagnostic
 *
 * Analyzes performance across different device types.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1564
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Device Performance Breakdown Diagnostic Class
 *
 * Monitors performance across desktop, mobile, tablet.
 *
 * @since 1.6035.1564
 */
class Diagnostic_Device_Performance_Breakdown extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'device-performance-breakdown';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Device Performance Breakdown';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes performance across different device types';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'real-user-monitoring';

	/**
	 * Mobile load time threshold (seconds)
	 *
	 * @var float
	 */
	private const MOBILE_THRESHOLD = 4.0;

	/**
	 * Desktop load time threshold (seconds)
	 *
	 * @var float
	 */
	private const DESKTOP_THRESHOLD = 2.5;

	/**
	 * Run the device performance diagnostic check.
	 *
	 * @since  1.6035.1564
	 * @return array|null Finding array if device performance issues detected, null otherwise.
	 */
	public static function check() {
		$device_performance = self::analyze_device_performance();

		if ( empty( $device_performance ) ) {
			return null; // Not enough data.
		}

		$issues = array();

		if ( isset( $device_performance['mobile'] ) && $device_performance['mobile'] > self::MOBILE_THRESHOLD ) {
			$issues[] = sprintf(
				'Mobile: %.1fs (threshold: %.1fs)',
				$device_performance['mobile'],
				self::MOBILE_THRESHOLD
			);
		}

		if ( isset( $device_performance['desktop'] ) && $device_performance['desktop'] > self::DESKTOP_THRESHOLD ) {
			$issues[] = sprintf(
				'Desktop: %.1fs (threshold: %.1fs)',
				$device_performance['desktop'],
				self::DESKTOP_THRESHOLD
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: device performance issues */
					__( 'Device-specific performance issues detected: %s. Optimize mobile performance especially.', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/optimize-mobile-performance',
				'meta'        => array(
					'device_breakdown' => $device_performance,
				),
			);
		}

		return null;
	}

	/**
	 * Analyze performance by device type.
	 *
	 * @since  1.6035.1564
	 * @return array Device performance data.
	 */
	private static function analyze_device_performance(): array {
		$performance = array();

		global $wpdb;

		if ( ! class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			return $performance;
		}

		$activity_table = $wpdb->prefix . 'wpshadow_activity';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$activity_table}'" ) !== $activity_table ) {
			return $performance;
		}

		$week_ago = time() - ( 7 * DAY_IN_SECONDS );

		// Analyze by device type from Activity Logger data.
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
					JSON_EXTRACT(meta, '$.device_type') as device_type,
					AVG(CAST(JSON_EXTRACT(meta, '$.load_time') AS DECIMAL(10,2))) as avg_load_time
				FROM {$activity_table} 
				WHERE action = %s AND created_at > %d
				GROUP BY JSON_EXTRACT(meta, '$.device_type')",
				'page_load_time_check',
				$week_ago
			)
		);

		if ( ! empty( $result ) ) {
			foreach ( $result as $row ) {
				$device = str_replace( '"', '', $row->device_type );
				$performance[ $device ] = (float) $row->avg_load_time;
			}
		}

		return $performance;
	}
}
