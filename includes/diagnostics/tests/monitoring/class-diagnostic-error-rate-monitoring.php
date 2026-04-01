<?php
/**
 * Error Rate Monitoring Diagnostic
 *
 * Checks for elevated PHP/HTTP error rates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Error Rate Monitoring Diagnostic Class
 *
 * Monitors error rates and alerts on spikes.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Error_Rate_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-rate-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Error Rate Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for elevated PHP/HTTP error rates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'downtime-prevention';

	/**
	 * Error rate threshold (percentage)
	 *
	 * @var float
	 */
	private const ERROR_RATE_THRESHOLD = 5.0;

	/**
	 * Run the error rate monitoring diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if error rate elevated, null otherwise.
	 */
	public static function check() {
		$error_rate = self::calculate_error_rate();

		if ( $error_rate > self::ERROR_RATE_THRESHOLD ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: error rate percentage, 2: threshold */
					__( 'Error rate is %1$.1f%% (threshold: %2$.1f%%). Investigate recent errors in debug log.', 'wpshadow' ),
					$error_rate,
					self::ERROR_RATE_THRESHOLD
				),
				'severity'    => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/investigate-high-error-rates?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'        => array(
					'current_error_rate'   => round( $error_rate, 2 ),
					'threshold'            => self::ERROR_RATE_THRESHOLD,
					'time_period'          => '24 hours',
				),
			);
		}

		return null;
	}

	/**
	 * Calculate error rate from log files.
	 *
	 * @since 0.6093.1200
	 * @return float Error rate as percentage.
	 */
	private static function calculate_error_rate(): float {
		$error_count = 0;
		$total_requests = 0;

		// Check debug log if enabled.
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$log_path = self::get_log_path();

			if ( $log_path && is_readable( $log_path ) ) {
				$log_lines = array_slice( file( $log_path ), -1000 ); // Last 1000 lines.

				foreach ( $log_lines as $line ) {
					$total_requests++;

					if ( strpos( $line, 'ERROR' ) !== false || strpos( $line, 'Fatal' ) !== false || strpos( $line, 'Warning' ) !== false ) {
						$error_count++;
					}
				}
			}
		}

		// If no log data, try Activity Logger option-backed records.
		if ( $total_requests === 0 && class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			$one_day_ago = time() - DAY_IN_SECONDS;
			$activity_log = get_option( \WPShadow\Core\Activity_Logger::OPTION_NAME, array() );

			if ( is_array( $activity_log ) ) {
				foreach ( $activity_log as $entry ) {
					$entry_time = isset( $entry['timestamp'] ) ? (int) $entry['timestamp'] : 0;

					if ( $entry_time <= $one_day_ago ) {
						continue;
					}

					++$total_requests;

					$action  = isset( $entry['action'] ) ? (string) $entry['action'] : '';
					$details = isset( $entry['details'] ) ? strtolower( (string) $entry['details'] ) : '';

					if ( false !== strpos( $action, 'error' ) || false !== strpos( $details, 'error' ) || false !== strpos( $details, 'fatal' ) || false !== strpos( $details, 'warning' ) ) {
						++$error_count;
					}
				}
			}
		}

		if ( $total_requests === 0 ) {
			return 0; // No data to calculate.
		}

		return ( $error_count / $total_requests ) * 100;
	}

	/**
	 * Get log file path.
	 *
	 * @since 0.6093.1200
	 * @return string|null Log file path.
	 */
	private static function get_log_path(): ?string {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			if ( is_string( WP_DEBUG_LOG ) ) {
				return WP_DEBUG_LOG;
			}

			return WP_CONTENT_DIR . '/debug.log';
		}

		return null;
	}
}
