<?php
/**
 * Unused JavaScript Detection Diagnostic
 *
 * Analyzes JavaScript files to detect code that is not executed,
 * indicating bloated bundles and unnecessary network overhead.
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
 * Diagnostic_Unused_JavaScript Class
 *
 * Detects high percentages of unused JavaScript that could be removed or lazy-loaded.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Unused_JavaScript extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unused-javascript-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'High Unused JavaScript Percentage Detected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects JavaScript files with high unused code percentages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Unused JS threshold warning (percentage)
	 *
	 * @var int
	 */
	const UNUSED_JS_WARNING = 40;

	/**
	 * Unused JS threshold critical (percentage)
	 *
	 * @var int
	 */
	const UNUSED_JS_CRITICAL = 60;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if high unused JS found, null otherwise.
	 */
	public static function check() {
		// Estimate unused JavaScript percentage
		$unused_js_percentage = self::estimate_unused_javascript();

		if ( $unused_js_percentage < self::UNUSED_JS_WARNING ) {
			// Acceptable JavaScript usage
			return null;
		}

		if ( $unused_js_percentage < self::UNUSED_JS_CRITICAL ) {
			// Warning level
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: percentage */
					__( 'Estimated %d%% of JavaScript is unused. This adds parse/execution overhead.', 'wpshadow' ),
					$unused_js_percentage
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/unused-javascript-detection',
				'family'       => self::$family,
				'meta'         => array(
					'estimated_unused_js_percentage' => $unused_js_percentage,
					'warning_threshold'              => self::UNUSED_JS_WARNING,
					'critical_threshold'             => self::UNUSED_JS_CRITICAL,
					'optimization_tips'              => array(
						__( 'Enable code splitting to load JS on-demand' ),
						__( 'Defer non-critical JavaScript' ),
						__( 'Remove unused dependencies from bundles' ),
						__( 'Lazy load features only needed on certain pages' ),
						__( 'Use dynamic imports for rarely-used code' ),
					),
				),
				'details'      => array(
					'issue'   => sprintf(
						/* translators: %d: percentage */
						__( '%d%% of your JavaScript is not executed on page load.', 'wpshadow' ),
						$unused_js_percentage
					),
					'impact'  => __( 'Unused JavaScript must be downloaded, parsed, and compiled by the browser. This adds significant Total Blocking Time.', 'wpshadow' ),
					'methods' => array(
						'Chrome Coverage Tool' => __( 'DevTools > Sources > Coverage tab shows code coverage' ),
						'Webpack Bundle Analyzer' => __( 'Visualize bundle size and identify large unused modules' ),
						'Dynamic Imports' => __( 'Use import() for code-splitting lazy loading' ),
					),
				),
			);
		}

		// Critical level
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: percentage */
				__( 'CRITICAL: Estimated %d%% of JavaScript is unused. Mobile performance severely impacted.', 'wpshadow' ),
				$unused_js_percentage
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/unused-javascript-detection',
			'family'       => self::$family,
			'meta'         => array(
				'estimated_unused_js_percentage' => $unused_js_percentage,
				'warning_threshold'              => self::UNUSED_JS_WARNING,
				'critical_threshold'             => self::UNUSED_JS_CRITICAL,
				'priority_actions'               => array(
					__( 'Audit all enqueued JavaScript immediately' ),
					__( 'Identify which JS can be lazy-loaded' ),
					__( 'Remove unused plugin/theme JavaScript' ),
					__( 'Split bundles to reduce initial load' ),
					__( 'Consider alternative plugins with smaller footprints' ),
				),
			),
			'details'      => array(
				'issue'       => sprintf(
					/* translators: %d: percentage */
					__( 'Over %d%% of JavaScript is not used.', 'wpshadow' ),
					$unused_js_percentage
				),
				'impact'      => __( 'CRITICAL - Unused JavaScript significantly increases:' ) . "\n• Parse time\n• Execution time\n• Main thread blocking\n• Mobile battery drain\n• Total Blocking Time\n• Mobile bounce rates",
				'quick_analysis' => array(
					__( '1. Load site in Chrome, open DevTools' ),
					__( '2. Go to Sources > Coverage tab' ),
					__( '3. Reload page and watch coverage data' ),
					__( '4. Identify scripts with >50% unused code' ),
					__( '5. Mark these for optimization or removal' ),
				),
				'optimization_strategies' => array(
					'Lazy Loading' => array(
						__( 'Modal dialogs - load only when opened' ),
						__( 'Below-the-fold content - load on scroll' ),
						__( 'Feature-specific code - load only on pages that need it' ),
						__( 'Use intersection observer for efficient lazy loading' ),
					),
					'Code Splitting' => array(
						__( 'Webpack code splitting with dynamic imports' ),
						__( 'Separate bundles for different page types' ),
						__( 'Async chunk loading strategy' ),
					),
					'Removal' => array(
						__( 'Remove polyfills for browsers you don\'t support' ),
						__( 'Remove unused dependencies' ),
						__( 'Disable plugins that add unused functionality' ),
					),
				),
			),
		);
	}

	/**
	 * Estimate unused JavaScript percentage.
	 *
	 * @since  1.2601.2148
	 * @return int Estimated percentage of unused JavaScript (0-100).
	 */
	private static function estimate_unused_javascript() {
		$total_js_size      = 0;
		$unused_js_estimate = 0;

		global $wp_scripts;

		if ( ! isset( $wp_scripts ) || ! isset( $wp_scripts->queue ) ) {
			return 0;
		}

		foreach ( $wp_scripts->queue as $handle ) {
			$script = $wp_scripts->registered[ $handle ];

			if ( ! isset( $script->src ) || empty( $script->src ) ) {
				continue;
			}

			$src = $script->src;

			// Get file size
			if ( strpos( $src, home_url() ) === 0 ) {
				$file_path = str_replace( home_url(), ABSPATH, $src );
				$file_path = strtok( $file_path, '?' );

				if ( file_exists( $file_path ) ) {
					$size = filesize( $file_path );
					$total_js_size += $size;

					// Estimate unused based on script type/purpose
					if ( file_get_contents( $file_path ) ) {
						// Estimate based on common patterns
						if ( strpos( $src, 'jquery' ) !== false ) {
							$unused_js_estimate += $size * 0.30; // jQuery usually 30% unused
						} elseif ( strpos( $src, 'polyfill' ) !== false ) {
							$unused_js_estimate += $size * 0.70; // Polyfills often not needed
						} elseif ( strpos( $src, 'vendor' ) !== false ) {
							$unused_js_estimate += $size * 0.50; // Vendor bundles often have unused deps
						} elseif ( strpos( $src, 'moment' ) !== false || strpos( $src, 'lodash' ) !== false ) {
							$unused_js_estimate += $size * 0.60; // These libraries often mostly unused
						} elseif ( strpos( $src, 'elementor' ) !== false || strpos( $src, 'wpml' ) !== false ) {
							$unused_js_estimate += $size * 0.45; // Page builder JS often page-specific
						} else {
							$unused_js_estimate += $size * 0.35; // Default estimate
						}
					}
				}
			}
		}

		if ( $total_js_size === 0 ) {
			return 0;
		}

		$unused_percentage = (int) ( ( $unused_js_estimate / $total_js_size ) * 100 );

		return min( $unused_percentage, 100 );
	}
}
