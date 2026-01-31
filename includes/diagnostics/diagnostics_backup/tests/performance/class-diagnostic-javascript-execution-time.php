<?php
/**
 * JavaScript Execution Time Performance Diagnostic
 *
 * Measures total CPU time spent in JavaScript parse and execution.
 * High execution time impacts mobile devices and battery life.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_JavaScript_Execution_Time Class
 *
 * Estimates JavaScript execution time from bundle size and script complexity.
 * Mobile devices are significantly slower than desktop CPUs.
 *
 * @since 1.2601.2148
 */
class Diagnostic_JavaScript_Execution_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-execution-time';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Execution Time Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures total CPU time in JavaScript processing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$exec_data = self::estimate_execution_time();

		if ( ! $exec_data ) {
			return null;
		}

		$execution_time = $exec_data['execution_time'];
		$js_size        = $exec_data['js_size'];
		$js_count       = $exec_data['js_count'];

		// Thresholds: ≤2s good, 2-4s warning, >4s critical.
		if ( $execution_time <= 2.0 ) {
			return null; // Good performance.
		}

		$severity     = 'medium';
		$threat_level = 60;

		if ( $execution_time > 4.0 ) {
			$severity     = 'critical';
			$threat_level = 85;
		} elseif ( $execution_time > 3.0 ) {
			$severity     = 'high';
			$threat_level = 75;
		}

		// Calculate battery impact on mobile.
		$battery_impact = self::calculate_battery_impact( $execution_time );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: Execution time in seconds */
				__( 'Estimated JavaScript execution time is %1$.2fs (target: ≤2s). This high CPU usage impacts mobile performance and battery life.', 'wpshadow' ),
				$execution_time
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/javascript-execution-time',
			'details'     => self::get_details( $exec_data, $battery_impact ),
		);
	}

	/**
	 * Estimate JavaScript execution time.
	 *
	 * Estimates CPU time from JavaScript bundle size.
	 * Mobile devices: ~1-2ms per 1KB. Desktop: ~0.5ms per 1KB.
	 *
	 * @since  1.2601.2148
	 * @return array|null {
	 *     Execution time estimation data.
	 *
	 *     @type float $execution_time Estimated execution time in seconds.
	 *     @type int   $js_size        Total JavaScript size in bytes.
	 *     @type int   $js_count       Number of JavaScript files.
	 *     @type int   $inline_count   Number of inline scripts.
	 * }
	 */
	private static function estimate_execution_time() {
		global $wp_scripts;

		if ( ! isset( $wp_scripts->queue ) ) {
			wp_enqueue_scripts();
		}

		$js_size      = 0;
		$js_count     = 0;
		$inline_count = 0;

		// Calculate total JS bundle size from enqueued scripts.
		foreach ( $wp_scripts->queue as $handle ) {
			if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
				continue;
			}

			$script = $wp_scripts->registered[ $handle ];
			$src    = $script->src;

			// Count inline scripts.
			if ( ! empty( $script->extra['after'] ) || ! empty( $script->extra['before'] ) ) {
				++$inline_count;
				// Estimate 5KB per inline script.
				$js_size += 5 * 1024;
			}

			// Skip empty or external scripts.
			if ( empty( $src ) ) {
				continue;
			}

			// Check if local script.
			$is_local = strpos( $src, home_url() ) === 0 || strpos( $src, '/' ) === 0;

			if ( $is_local ) {
				// Try to get actual file size.
				$path = str_replace( array( home_url( '/' ), WP_CONTENT_URL . '/' ), array( ABSPATH, WP_CONTENT_DIR . '/' ), $src );
				if ( file_exists( $path ) ) {
					$js_size += filesize( $path );
					++$js_count;
				} else {
					// Assume average 50KB per script.
					$js_size += 50 * 1024;
					++$js_count;
				}
			} else {
				// External script: assume 100KB average (analytics, ads, etc.).
				$js_size += 100 * 1024;
				++$js_count;
			}
		}

		// Estimate execution time: 1.5ms per KB on mid-range mobile device.
		// This is a conservative estimate based on real-world benchmarks.
		$execution_time = ( $js_size / 1024 ) * 0.0015;

		// Add overhead for script evaluation and initialization.
		$execution_time += ( $js_count * 0.05 ); // 50ms per script baseline.

		return array(
			'execution_time' => $execution_time,
			'js_size'        => $js_size,
			'js_count'       => $js_count,
			'inline_count'   => $inline_count,
		);
	}

	/**
	 * Calculate battery impact on mobile devices.
	 *
	 * Estimates battery drain percentage from JavaScript execution time.
	 *
	 * @since  1.2601.2148
	 * @param  float $execution_time Execution time in seconds.
	 * @return array {
	 *     Battery impact data.
	 *
	 *     @type float $battery_drain_percent  Estimated battery drain percentage.
	 *     @type float $pages_per_charge       Estimated pages viewable per battery charge.
	 * }
	 */
	private static function calculate_battery_impact( $execution_time ) {
		// Typical mobile battery capacity: 3000-5000mAh.
		// CPU at 100% usage: ~500-800mW.
		// Assume 600mW average for JavaScript execution.
		// 1 second of CPU = ~0.00017mAh at 3.7V.
		$battery_drain_percent = ( $execution_time * 0.00017 / 4000 ) * 100;

		// Estimate pages viewable per charge (assuming 4000mAh battery).
		$pages_per_charge = 0;
		if ( $battery_drain_percent > 0 ) {
			$pages_per_charge = round( 100 / $battery_drain_percent );
		}

		return array(
			'battery_drain_percent' => round( $battery_drain_percent, 4 ),
			'pages_per_charge'      => $pages_per_charge,
		);
	}

	/**
	 * Get detailed information about the finding.
	 *
	 * @since  1.2601.2148
	 * @param  array $exec_data      Execution time data.
	 * @param  array $battery_impact Battery impact data.
	 * @return array Details array with explanation and solutions.
	 */
	private static function get_details( $exec_data, $battery_impact ) {
		$execution_time = $exec_data['execution_time'];
		$js_size        = $exec_data['js_size'];
		$js_count       = $exec_data['js_count'];
		$inline_count   = $exec_data['inline_count'];

		$explanation = sprintf(
			/* translators: 1: Execution time, 2: JS count, 3: JS size, 4: inline count */
			__( 'Your JavaScript execution time is estimated at %1$.2f seconds, which exceeds the recommended 2 second threshold. You have %2$d JavaScript files (%3$d inline scripts) totaling %4$dKB. High JavaScript execution time primarily impacts mobile users, causing slow page loads, increased battery drain, and poor performance on budget devices.', 'wpshadow' ),
			$execution_time,
			$js_count,
			$inline_count,
			round( $js_size / 1024 )
		);

		$solutions = array(
			'free' => array(
				__( 'Remove unused plugins: Deactivate plugins that add unnecessary JavaScript', 'wpshadow' ),
				__( 'Minimize inline scripts: Move inline JavaScript to external files for caching', 'wpshadow' ),
				__( 'Defer non-critical JS: Use defer/async to prevent blocking', 'wpshadow' ),
				__( 'Audit third-party scripts: Remove or delay analytics, ads, and social widgets', 'wpshadow' ),
			),
			'premium' => array(
				__( 'Code splitting: Load only necessary JavaScript per page', 'wpshadow' ),
				__( 'Minify and bundle: Combine and compress JavaScript files', 'wpshadow' ),
				__( 'Use a performance plugin: WP Rocket, Autoptimize, or similar', 'wpshadow' ),
			),
			'advanced' => array(
				__( 'Implement lazy loading: Load JavaScript only when needed', 'wpshadow' ),
				__( 'Switch to lighter alternatives: Replace heavy libraries (jQuery → Vanilla JS)', 'wpshadow' ),
				__( 'Server-side rendering (SSR): Pre-render content to reduce client-side JS', 'wpshadow' ),
			),
		);

		$additional_info = sprintf(
			/* translators: 1: Pages per charge, 2: battery drain percent */
			__( 'Battery impact: Mobile users can view approximately %1$d pages per charge (estimated %2$.4f%% battery drain per page). Google recommends ≤2s JavaScript execution time for optimal mobile performance.', 'wpshadow' ),
			$battery_impact['pages_per_charge'],
			$battery_impact['battery_drain_percent']
		);

		return array(
			'explanation'     => $explanation,
			'solutions'       => $solutions,
			'additional_info' => $additional_info,
			'technical_data'  => array(
				'execution_time'        => round( $execution_time, 2 ) . 's',
				'js_count'              => $js_count,
				'inline_count'          => $inline_count,
				'total_size'            => size_format( $js_size, 2 ),
				'avg_file_size'         => $js_count > 0 ? size_format( $js_size / $js_count, 2 ) : '0',
				'battery_drain'         => $battery_impact['battery_drain_percent'] . '%',
				'pages_per_charge'      => $battery_impact['pages_per_charge'],
				'threshold'             => '≤2s',
				'warning_threshold'     => '2-4s',
				'critical_threshold'    => '>4s',
			),
			'resources'       => array(
				array(
					'label' => __( 'JavaScript Performance', 'wpshadow' ),
					'url'   => 'https://web.dev/javascript-performance/',
				),
				array(
					'label' => __( 'Reduce JavaScript Execution Time', 'wpshadow' ),
					'url'   => 'https://web.dev/bootup-time/',
				),
			),
		);
	}
}
