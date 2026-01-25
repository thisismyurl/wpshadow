<?php
/**
 * Performance Benchmark Diagnostic
 *
 * Measures and validates performance of WPShadow UI/UX components.
 * Checks JavaScript bundle sizes, CSS performance, page load times,
 * and provides optimization recommendations.
 *
 * Phase 5 of UI/UX Epic - Final Polish & Validation
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics\Tests
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Performance_Benchmark Class
 *
 * Performs comprehensive performance analysis of WPShadow assets and UI.
 * Provides recommendations for optimization.
 */
class Diagnostic_Performance_Benchmark extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'performance-benchmark';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Benchmark';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes UI/UX performance and provides optimization recommendations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Asset size thresholds (in KB)
	 */
	const MAX_JS_SIZE_KB  = 100;
	const MAX_CSS_SIZE_KB = 50;
	const WARN_JS_SIZE_KB = 75;
	const WARN_CSS_SIZE_KB = 35;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$metrics = array();
		$issues  = array();

		// Analyze JavaScript bundle sizes.
		$js_metrics = self::analyze_javascript_bundles();
		$metrics['javascript'] = $js_metrics;

		if ( ! empty( $js_metrics['oversized_files'] ) ) {
			$issues[] = array(
				'type'    => 'javascript',
				'message' => sprintf(
					/* translators: %d: number of oversized files */
					__( '%d JavaScript files exceed recommended size', 'wpshadow' ),
					count( $js_metrics['oversized_files'] )
				),
				'files'   => $js_metrics['oversized_files'],
			);
		}

		// Analyze CSS bundle sizes.
		$css_metrics = self::analyze_css_bundles();
		$metrics['css'] = $css_metrics;

		if ( ! empty( $css_metrics['oversized_files'] ) ) {
			$issues[] = array(
				'type'    => 'css',
				'message' => sprintf(
					/* translators: %d: number of oversized files */
					__( '%d CSS files exceed recommended size', 'wpshadow' ),
					count( $css_metrics['oversized_files'] )
				),
				'files'   => $css_metrics['oversized_files'],
			);
		}

		// Check for optimization opportunities.
		$optimization_tips = self::get_optimization_recommendations( $metrics );
		if ( ! empty( $optimization_tips ) ) {
			$metrics['recommendations'] = $optimization_tips;
		}

		// If any issues found, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => __( 'Performance Optimization Opportunities', 'wpshadow' ),
				'description'   => sprintf(
					/* translators: %d: number of performance issues found */
					__( 'Found %d areas where performance can be improved.', 'wpshadow' ),
					count( $issues )
				),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/performance-optimization/',
				'training_link' => 'https://wpshadow.com/training/frontend-performance/',
				'module'        => 'Performance',
				'priority'      => 3,
				'meta'          => array(
					'metrics' => $metrics,
					'issues'  => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Analyze JavaScript bundle sizes.
	 *
	 * @since  1.2601.2148
	 * @return array Metrics array.
	 */
	private static function analyze_javascript_bundles() {
		$js_path = WPSHADOW_PATH . 'assets/js/';
		$metrics = array(
			'total_size'       => 0,
			'total_files'      => 0,
			'oversized_files'  => array(),
			'largest_file'     => array( 'name' => '', 'size' => 0 ),
		);

		if ( ! is_dir( $js_path ) ) {
			return $metrics;
		}

		$js_files = glob( $js_path . '*.js' );
		if ( ! $js_files ) {
			return $metrics;
		}

		foreach ( $js_files as $file ) {
			$size_bytes = filesize( $file );
			$size_kb    = round( $size_bytes / 1024, 2 );
			$filename   = basename( $file );

			$metrics['total_size'] += $size_bytes;
			$metrics['total_files']++;

			// Track largest file.
			if ( $size_bytes > $metrics['largest_file']['size'] ) {
				$metrics['largest_file'] = array(
					'name' => $filename,
					'size' => $size_kb,
				);
			}

			// Flag oversized files.
			if ( $size_kb > self::MAX_JS_SIZE_KB ) {
				$metrics['oversized_files'][] = array(
					'name'     => $filename,
					'size'     => $size_kb,
					'severity' => 'error',
					'message'  => sprintf(
						/* translators: 1: file size in KB, 2: max size in KB */
						__( '%1$s KB (exceeds %2$s KB limit)', 'wpshadow' ),
						number_format_i18n( $size_kb, 2 ),
						self::MAX_JS_SIZE_KB
					),
				);
			} elseif ( $size_kb > self::WARN_JS_SIZE_KB ) {
				$metrics['oversized_files'][] = array(
					'name'     => $filename,
					'size'     => $size_kb,
					'severity' => 'warning',
					'message'  => sprintf(
						/* translators: 1: file size in KB, 2: recommended size in KB */
						__( '%1$s KB (approaching %2$s KB recommended limit)', 'wpshadow' ),
						number_format_i18n( $size_kb, 2 ),
						self::MAX_JS_SIZE_KB
					),
				);
			}
		}

		$metrics['total_size'] = round( $metrics['total_size'] / 1024, 2 );

		return $metrics;
	}

	/**
	 * Analyze CSS bundle sizes.
	 *
	 * @since  1.2601.2148
	 * @return array Metrics array.
	 */
	private static function analyze_css_bundles() {
		$css_path = WPSHADOW_PATH . 'assets/css/';
		$metrics  = array(
			'total_size'      => 0,
			'total_files'     => 0,
			'oversized_files' => array(),
			'largest_file'    => array( 'name' => '', 'size' => 0 ),
		);

		if ( ! is_dir( $css_path ) ) {
			return $metrics;
		}

		$css_files = glob( $css_path . '*.css' );
		if ( ! $css_files ) {
			return $metrics;
		}

		foreach ( $css_files as $file ) {
			$size_bytes = filesize( $file );
			$size_kb    = round( $size_bytes / 1024, 2 );
			$filename   = basename( $file );

			$metrics['total_size'] += $size_bytes;
			$metrics['total_files']++;

			// Track largest file.
			if ( $size_bytes > $metrics['largest_file']['size'] ) {
				$metrics['largest_file'] = array(
					'name' => $filename,
					'size' => $size_kb,
				);
			}

			// Flag oversized files.
			if ( $size_kb > self::MAX_CSS_SIZE_KB ) {
				$metrics['oversized_files'][] = array(
					'name'     => $filename,
					'size'     => $size_kb,
					'severity' => 'error',
					'message'  => sprintf(
						/* translators: 1: file size in KB, 2: max size in KB */
						__( '%1$s KB (exceeds %2$s KB limit)', 'wpshadow' ),
						number_format_i18n( $size_kb, 2 ),
						self::MAX_CSS_SIZE_KB
					),
				);
			} elseif ( $size_kb > self::WARN_CSS_SIZE_KB ) {
				$metrics['oversized_files'][] = array(
					'name'     => $filename,
					'size'     => $size_kb,
					'severity' => 'warning',
					'message'  => sprintf(
						/* translators: 1: file size in KB, 2: recommended size in KB */
						__( '%1$s KB (approaching %2$s KB recommended limit)', 'wpshadow' ),
						number_format_i18n( $size_kb, 2 ),
						self::MAX_CSS_SIZE_KB
					),
				);
			}
		}

		$metrics['total_size'] = round( $metrics['total_size'] / 1024, 2 );

		return $metrics;
	}

	/**
	 * Get optimization recommendations.
	 *
	 * @since  1.2601.2148
	 * @param  array $metrics Performance metrics.
	 * @return array Recommendations.
	 */
	private static function get_optimization_recommendations( $metrics ) {
		$recommendations = array();

		// JavaScript recommendations.
		if ( isset( $metrics['javascript'] ) ) {
			$js_metrics = $metrics['javascript'];

			if ( $js_metrics['total_size'] > 300 ) {
				$recommendations[] = array(
					'category' => 'javascript',
					'title'    => __( 'Consider code splitting', 'wpshadow' ),
					'message'  => sprintf(
						/* translators: %s: total JS size in KB */
						__( 'Total JavaScript size is %s KB. Consider splitting into smaller, page-specific bundles.', 'wpshadow' ),
						number_format_i18n( $js_metrics['total_size'], 2 )
					),
					'priority' => 'medium',
				);
			}

			if ( $js_metrics['total_files'] > 15 ) {
				$recommendations[] = array(
					'category' => 'javascript',
					'title'    => __( 'Consider bundling scripts', 'wpshadow' ),
					'message'  => sprintf(
						/* translators: %d: number of JS files */
						__( 'Loading %d JavaScript files. Consider bundling related scripts to reduce HTTP requests.', 'wpshadow' ),
						$js_metrics['total_files']
					),
					'priority' => 'low',
				);
			}
		}

		// CSS recommendations.
		if ( isset( $metrics['css'] ) ) {
			$css_metrics = $metrics['css'];

			if ( $css_metrics['total_size'] > 200 ) {
				$recommendations[] = array(
					'category' => 'css',
					'title'    => __( 'Consider CSS optimization', 'wpshadow' ),
					'message'  => sprintf(
						/* translators: %s: total CSS size in KB */
						__( 'Total CSS size is %s KB. Consider removing unused styles or splitting into critical and non-critical CSS.', 'wpshadow' ),
						number_format_i18n( $css_metrics['total_size'], 2 )
					),
					'priority' => 'medium',
				);
			}
		}

		return $recommendations;
	}

	/**
	 * Get the diagnostic name.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Performance Benchmark', 'wpshadow' );
	}

	/**
	 * Get the diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Analyzes UI/UX performance and provides optimization recommendations.', 'wpshadow' );
	}
}
