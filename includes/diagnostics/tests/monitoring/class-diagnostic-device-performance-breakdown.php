<?php
/**
 * Device Performance Breakdown Diagnostic
 *
 * Analyzes performance across different device types.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
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
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
	 * @return array Device performance data.
	 */
	private static function analyze_device_performance(): array {
		$performance = array();

		if ( ! class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			return $performance;
		}

		$week_ago = time() - ( 7 * DAY_IN_SECONDS );
		$activity_log = get_option( \WPShadow\Core\Activity_Logger::OPTION_NAME, array() );
		$totals       = array();
		$counts       = array();

		if ( ! is_array( $activity_log ) ) {
			return $performance;
		}

		foreach ( $activity_log as $entry ) {
			$entry_time = isset( $entry['timestamp'] ) ? (int) $entry['timestamp'] : 0;
			$action     = isset( $entry['action'] ) ? (string) $entry['action'] : '';

			if ( $entry_time <= $week_ago || 'page_load_time_check' !== $action ) {
				continue;
			}

			$metadata = isset( $entry['metadata'] ) && is_array( $entry['metadata'] ) ? $entry['metadata'] : array();
			$device   = isset( $metadata['device_type'] ) ? sanitize_key( (string) $metadata['device_type'] ) : '';
			$load     = isset( $metadata['load_time'] ) ? (float) $metadata['load_time'] : 0.0;

			if ( '' === $device || $load <= 0 ) {
				continue;
			}

			if ( ! isset( $totals[ $device ] ) ) {
				$totals[ $device ] = 0.0;
				$counts[ $device ] = 0;
			}

			$totals[ $device ] += $load;
			++$counts[ $device ];
		}

		foreach ( $totals as $device => $total ) {
			if ( ! empty( $counts[ $device ] ) ) {
				$performance[ $device ] = $total / $counts[ $device ];
			}
		}

		return $performance;
	}
}
