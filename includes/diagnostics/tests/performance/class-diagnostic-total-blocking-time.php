<?php
/**
 * Total Blocking Time (TBT) Performance Diagnostic
 *
 * Measures the total time the main thread is blocked by long-running JavaScript tasks.
 * High TBT indicates heavy JavaScript processing that blocks user interactions.
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
 * Diagnostic_Total_Blocking_Time Class
 *
 * Estimates main thread blocking time from JavaScript bundle size and plugin complexity.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Total_Blocking_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'total-blocking-time';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Total Blocking Time (TBT) Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Estimates main thread blocking time from JavaScript';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * TBT threshold for good performance (milliseconds)
	 *
	 * @var int
	 */
	const TBT_GOOD = 200;

	/**
	 * TBT threshold for acceptable performance (milliseconds)
	 *
	 * @var int
	 */
	const TBT_ACCEPTABLE = 400;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Estimate TBT from various factors
		$estimated_tbt = self::estimate_main_thread_blocking();

		if ( $estimated_tbt <= self::TBT_GOOD ) {
			// Good performance
			return null;
		}

		if ( $estimated_tbt <= self::TBT_ACCEPTABLE ) {
			// Needs improvement
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: TBT value in milliseconds */
					__( 'Estimated Total Blocking Time: %dms. Long JavaScript tasks are blocking user interactions.', 'wpshadow' ),
					$estimated_tbt
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/total-blocking-time',
				'family'       => self::$family,
				'meta'         => array(
					'estimated_tbt_ms'   => $estimated_tbt,
					'threshold_good'     => self::TBT_GOOD,
					'threshold_poor'     => self::TBT_ACCEPTABLE,
					'blocking_sources'   => self::identify_blocking_sources(),
					'optimization_tips'  => array(
						__( 'Break long JavaScript tasks into smaller chunks' ),
						__( 'Use code splitting to load only necessary code' ),
						__( 'Defer non-critical third-party scripts' ),
						__( 'Move heavy computations to Web Workers' ),
						__( 'Remove unused JavaScript' ),
					),
				),
				'details'      => array(
					'issue'   => __( 'Long-running JavaScript tasks are blocking the main thread.', 'wpshadow' ),
					'impact'  => __( 'Users experience lag when trying to interact with the page. Buttons feel unresponsive, clicks are delayed.', 'wpshadow' ),
					'what_blocks' => array(
						__( 'JavaScript parsing and execution' ),
						__( 'DOM manipulation and layout calculation' ),
						__( 'Third-party scripts (analytics, ads)' ),
						__( 'Large plugin initialization' ),
						__( 'Network requests being parsed' ),
					),
				),
			);
		}

		// Poor performance (critical)
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: TBT value in milliseconds */
				__( 'Total Blocking Time is critically high: %dms. Page interactions are severely delayed.', 'wpshadow' ),
				$estimated_tbt
			),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/total-blocking-time',
			'family'       => self::$family,
			'meta'         => array(
				'estimated_tbt_ms'   => $estimated_tbt,
				'threshold_good'     => self::TBT_GOOD,
				'threshold_poor'     => self::TBT_ACCEPTABLE,
				'blocking_sources'   => self::identify_blocking_sources(),
				'critical_actions'   => array(
					__( 'Identify which plugins/scripts cause the most blocking' ),
					__( 'Disable non-essential plugins' ),
					__( 'Defer third-party scripts to after interaction' ),
					__( 'Consider lightweight alternatives' ),
					__( 'Run DevTools Performance profiling' ),
				),
			),
			'details'      => array(
				'issue'       => __( 'Main thread is blocked for extended periods.', 'wpshadow' ),
				'impact'      => __( 'CRITICAL - Users cannot interact with the page smoothly. Mobile users particularly affected. High bounce rate likely.' ),
				'measurement' => __( 'This is an estimate based on JavaScript bundle size and complexity. Actual measurement requires real user monitoring.', 'wpshadow' ),
				'next_steps'  => array(
					__( '1. Use Chrome DevTools Performance tab to profile actual blocking' ),
					__( '2. Look for "long tasks" - tasks longer than 50ms' ),
					__( '3. Identify which script/plugin causes each task' ),
					__( '4. Optimize or remove the worst offenders' ),
				),
			),
		);
	}

	/**
	 * Estimate main thread blocking time from various factors.
	 *
	 * @since  1.2601.2148
	 * @return int Estimated TBT in milliseconds.
	 */
	private static function estimate_main_thread_blocking() {
		$blocking_time = 0;

		// Factor 1: JavaScript bundle size
		global $wp_scripts;
		$js_size = 0;

		if ( isset( $wp_scripts ) && isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( isset( $wp_scripts->registered[ $handle ] ) ) {
					$script = $wp_scripts->registered[ $handle ];
					if ( isset( $script->src ) && ! empty( $script->src ) ) {
						if ( strpos( $script->src, home_url() ) === 0 ) {
							$file_path = str_replace( home_url(), ABSPATH, $script->src );
							$file_path = strtok( $file_path, '?' );

							if ( file_exists( $file_path ) ) {
								$js_size += filesize( $file_path );
							}
						}
					}
				}
			}
		}

		// Estimate parsing time (rough estimate: 1ms per KB on average CPU)
		$blocking_time += (int) ( $js_size / 1024 );

		// Factor 2: Number of enqueued scripts (more scripts = more parsing/execution overhead)
		if ( isset( $wp_scripts ) && isset( $wp_scripts->queue ) ) {
			$script_count  = count( $wp_scripts->queue );
			$blocking_time += $script_count * 5; // Add 5ms per script
		}

		// Factor 3: Active plugins (heavy plugins like WooCommerce, Elementor, etc.)
		$plugins = get_plugins();
		$heavy_plugins = array( 'elementor', 'woocommerce', 'woo-commerce', 'divi', 'beaver-builder' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if ( is_plugin_active( $plugin_file ) ) {
				foreach ( $heavy_plugins as $heavy ) {
					if ( strpos( $plugin_file, $heavy ) !== false ) {
						$blocking_time += 40; // Heavy plugins add ~40ms
					}
				}
			}
		}

		// Factor 4: Third-party scripts (Google Analytics, Facebook Pixel, etc.)
		// Estimate based on number of external scripts
		if ( isset( $wp_scripts ) && isset( $wp_scripts->queue ) ) {
			$external_count = 0;
			foreach ( $wp_scripts->queue as $handle ) {
				if ( isset( $wp_scripts->registered[ $handle ] ) ) {
					$script = $wp_scripts->registered[ $handle ];
					if ( isset( $script->src ) && strpos( $script->src, home_url() ) === false && ! empty( $script->src ) ) {
						$external_count++;
					}
				}
			}
			$blocking_time += $external_count * 30; // Each external script adds ~30ms
		}

		// Factor 5: AJAX activity (if high AJAX requests during page load)
		if ( wp_doing_ajax() ) {
			$blocking_time += 50;
		}

		return min( $blocking_time, 2000 ); // Cap at 2000ms for display purposes
	}

	/**
	 * Identify sources of main thread blocking.
	 *
	 * @since  1.2601.2148
	 * @return array List of blocking sources with impact estimates.
	 */
	private static function identify_blocking_sources() {
		$sources = array();

		// Check JavaScript size
		global $wp_scripts;
		$js_size = 0;

		if ( isset( $wp_scripts ) && isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( isset( $wp_scripts->registered[ $handle ] ) ) {
					$script = $wp_scripts->registered[ $handle ];
					if ( isset( $script->src ) && strpos( $script->src, home_url() ) === 0 && ! empty( $script->src ) ) {
						$file_path = str_replace( home_url(), ABSPATH, $script->src );
						$file_path = strtok( $file_path, '?' );

						if ( file_exists( $file_path ) ) {
							$size = filesize( $file_path );
							if ( $size > 50000 ) { // > 50KB
								$sources[] = array(
									'source' => $handle,
									'impact' => 'high',
									'size_kb' => round( $size / 1024 ),
								);
							}
						}
					}
				}
			}
		}

		// Check heavy plugins
		$plugins = get_plugins();
		$heavy_plugins = array( 'elementor', 'woocommerce', 'divi', 'beaver-builder' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if ( is_plugin_active( $plugin_file ) ) {
				foreach ( $heavy_plugins as $heavy ) {
					if ( strpos( $plugin_file, $heavy ) !== false ) {
						$sources[] = array(
							'source' => $plugin_data['Name'],
							'impact' => 'high',
							'type'   => 'plugin',
						);
					}
				}
			}
		}

		return array_slice( $sources, 0, 5 ); // Return top 5
	}
}
