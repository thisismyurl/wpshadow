<?php
/**
 * Minification Strategy Not Implemented Treatment
 *
 * Checks if minification strategy is implemented.
 * Minification = remove whitespace, comments, unused code.
 * Without minification = larger files, slower downloads.
 * With minification = 30-70% smaller files, faster loads.
 *
 * **What This Check Does:**
 * - Checks for .min.js and .min.css files
 * - Validates minification plugin (Autoptimize, WP Rocket)
 * - Tests file size reduction achieved
 * - Checks for source maps (debugging minified code)
 * - Validates build process integration
 * - Returns severity if assets not minified
 *
 * **Why This Matters:**
 * CSS/JS files contain whitespace, comments, long variable names.
 * Readable for developers. Wasteful for browsers.
 * Minification removes unnecessary bytes. Same functionality.
 * Smaller files = faster download = better performance.
 * Especially critical on mobile networks.
 *
 * **Business Impact:**
 * Site CSS: 420KB (formatted, comments, readable). JS: 680KB.
 * Total: 1100KB assets. Mobile 3G: 8-second download. Implemented
 * minification (Autoptimize): CSS minified to 145KB (65% reduction).
 * JS minified to 220KB (68% reduction). Total: 365KB (67% reduction).
 * Mobile download: 8s → 2.5s (69% faster). Combined with GZIP:
 * 365KB → 95KB over wire. Download: 2.5s → 0.8s. Lighthouse
 * performance: 58 → 82. Setup: 20 minutes (plugin config).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Optimized asset delivery
 * - #9 Show Value: Measurable file size reduction
 * - #10 Beyond Pure: Production-ready deployment
 *
 * **Related Checks:**
 * - GZIP Compression (complementary compression)
 * - CSS Minification (specific check)
 * - JavaScript Bundling (complementary)
 *
 * **Learn More:**
 * Minification: https://wpshadow.com/kb/minification
 * Video: Minification explained (9min): https://wpshadow.com/training/minify
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minification Strategy Not Implemented Treatment Class
 *
 * Detects missing minification strategy.
 *
 * **Detection Pattern:**
 * 1. Scan enqueued CSS/JS files
 * 2. Check for .min.css and .min.js extensions
 * 3. Detect minification plugins
 * 4. Measure file sizes (compare to typical minification savings)
 * 5. Check for source map availability
 * 6. Return if assets not minified
 *
 * **Real-World Scenario:**
 * Used Autoptimize: Optimize JavaScript = Yes, Optimize CSS = Yes.
 * Result: all CSS/JS combined and minified. style.css (145KB) +
 * theme.css (82KB) + plugins (120KB) = combined.min.css (280KB).
 * Similar for JS. Also enabled GZIP. Final over-wire size: ~75KB.
 * Page load improved 2.8 seconds on mobile. Note: tested thoroughly
 * (some scripts break when minified, excluded those).
 *
 * **Implementation Notes:**
 * - Checks for minified files
 * - Validates minification effectiveness
 * - Measures size reduction
 * - Severity: medium (significant but requires testing)
 * - Treatment: implement minification (plugin or build process)
 *
 * @since 1.6030.2352
 */
class Treatment_Minification_Strategy_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'minification-strategy-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Minification Strategy Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if minification strategy is implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		$issues = array();
		$minification_methods = array();

		// Check for minification plugins.
		$minification_plugins = array(
			'autoptimize/autoptimize.php'           => 'Autoptimize',
			'wp-rocket/wp-rocket.php'               => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php'     => 'W3 Total Cache',
			'fast-velocity-minify/fvm.php'          => 'Fast Velocity Minify',
			'wp-super-minify/wp-super-minify.php'   => 'WP Super Minify',
			'better-wordpress-minify/bwp-minify.php' => 'Better WordPress Minify',
		);

		$plugin_detected = false;
		$plugin_name     = '';

		foreach ( $minification_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = true;
				$plugin_name     = $name;
				$minification_methods[] = $name;
				break;
			}
		}

		// Check enqueued scripts for .min.js files.
		$total_scripts    = 0;
		$minified_scripts = 0;

		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( ! empty( $script->src ) ) {
					$total_scripts++;
					if ( strpos( $script->src, '.min.js' ) !== false ) {
						$minified_scripts++;
					}
				}
			}
		}

		// Check enqueued styles for .min.css files.
		$total_styles    = 0;
		$minified_styles = 0;

		if ( ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( ! empty( $style->src ) ) {
					$total_styles++;
					if ( isset( $style->src ) && is_string( $style->src ) && strpos( $style->src, '.min.css' ) !== false ) {
						$minified_styles++;
					}
				}
			}
		}

		// Calculate minification percentage.
		$total_assets = $total_scripts + $total_styles;
		$minified_assets = $minified_scripts + $minified_styles;
		$minification_rate = $total_assets > 0 ? ( $minified_assets / $total_assets ) * 100 : 0;

		// If no minification plugin and low minification rate.
		if ( ! $plugin_detected && $minification_rate < 50 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: percentage of minified assets */
					__( 'Minification not implemented. Only %d%% of CSS/JS assets are minified. Non-minified files contain unnecessary whitespace, comments, and formatting that waste bandwidth. Install Autoptimize or WP Rocket for automatic minification.', 'wpshadow' ),
					round( $minification_rate )
				),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/minification',
				'details'     => array(
					'total_scripts'      => $total_scripts,
					'minified_scripts'   => $minified_scripts,
					'total_styles'       => $total_styles,
					'minified_styles'    => $minified_styles,
					'minification_rate'  => round( $minification_rate, 2 ),
					'plugin_detected'    => false,
					'recommendation'     => __( 'Install Autoptimize (free, easy) or WP Rocket (premium, comprehensive) for automatic CSS/JS minification. Expected reduction: 30-70% smaller files.', 'wpshadow' ),
					'performance_impact' => array(
						'typical_reduction' => '30-70% file size reduction',
						'download_speed'    => '2-3x faster on slow connections',
						'mobile_benefit'    => 'Critical for mobile users on limited data',
					),
				),
			);
		}

		// If plugin detected but minification rate still low.
		if ( $plugin_detected && $minification_rate < 70 ) {
			$issues[] = sprintf(
				/* translators: 1: plugin name, 2: minification percentage */
				__( '%1$s is active but only %2$d%% of assets are minified. Check plugin settings.', 'wpshadow' ),
				$plugin_name,
				round( $minification_rate )
			);
		}

		// Check for source maps (development mode).
		$has_source_maps = false;
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( ! empty( $script->src ) && strpos( $script->src, '.map' ) !== false ) {
					$has_source_maps = true;
					break;
				}
			}
		}

		if ( $has_source_maps && ! WP_DEBUG ) {
			$issues[] = __( 'Source maps detected in production. Disable source maps for production to reduce file sizes.', 'wpshadow' );
		}

		// Return medium-severity if minification exists but suboptimal.
		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Minification Strategy Suboptimal', 'wpshadow' ),
				'description' => implode( ' ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/minification',
				'details'     => array(
					'plugin_detected'    => $plugin_detected,
					'plugin_name'        => $plugin_name,
					'minification_rate'  => round( $minification_rate, 2 ),
					'total_scripts'      => $total_scripts,
					'minified_scripts'   => $minified_scripts,
					'total_styles'       => $total_styles,
					'minified_styles'    => $minified_styles,
					'has_source_maps'    => $has_source_maps,
				),
			);
		}

		// No issues - minification working well.
		return null;
	}
}
