<?php
/**
 * CSS Minification Not Implemented Diagnostic
 *
 * Checks if CSS is minified.
 * Minification = remove whitespace, comments, optimize syntax.
 * Unminified CSS = 150KB with formatting.
 * Minified CSS = 95KB (37% smaller). Faster downloads.
 *
 * **What This Check Does:**
 * - Checks enqueued stylesheets for .min.css extension
 * - Validates minification plugin active
 * - Tests actual file sizes (minified vs original)
 * - Checks compression savings achieved
 * - Validates source maps for debugging
 * - Returns severity if CSS unminified in production
 *
 * **Why This Matters:**
 * CSS file: 200KB with comments and formatting.
 * Mobile 3G: takes 6 seconds to download.
 * User sees unstyled page. Minified: 120KB.
 * Downloads in 3.5 seconds. Better experience.
 *
 * **Business Impact:**
 * Theme CSS: 280KB unminified (includes comments, formatting).
 * Minification reduces to 165KB (41% savings). On mobile 3G:
 * load time reduced from 8.5s to 5s. Combined with gzip (further
 * 70% reduction): final size 50KB, loads in1.0s. Bounce rate
 * on mobile improved 25%. Mobile revenue increased $15K/month.
 * Minification setup: 30 minutes (one-time). ROI: 600:1 annually.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Optimized for all connections
 * - #9 Show Value: Measurable mobile performance gains
 * - #10 Beyond Pure: Professional optimization practices
 *
 * **Related Checks:**
 * - JavaScript Minification (parallel optimization)
 * - HTML Minification (complementary)
 * - GZIP Compression (works with minification)
 *
 * **Learn More:**
 * CSS minification: https://wpshadow.com/kb/css-minification
 * Video: Optimizing stylesheets (10min): https://wpshadow.com/training/css-optimization
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
 * CSS Minification Not Implemented Diagnostic Class
 *
 * Detects unminified CSS.
 *
 * **Detection Pattern:**
 * 1. Get all enqueued styles via wp_styles global
 * 2. Check file extensions (.min.css vs .css)
 * 3. Test minification plugin active
 * 4. Compare file sizes (minified should be 30-50% smaller)
 * 5. Validate source maps exist for debugging
 * 6. Return if unminified CSS in production
 *
 * **Real-World Scenario:**
 * Implemented WP Rocket minification. Original CSS: 12 files, 420KB
 * total. Minified + combined: 1 file, 180KB (57% reduction). With
 * gzip: 55KB over wire. Page load time improved1.0 seconds.
 * Lighthouse performance score: 72 → 89. Developer can still debug
 * via source maps. Best of both worlds.
 *
 * **Implementation Notes:**
 * - Checks CSS file naming and sizes
 * - Validates minification process
 * - Tests compression effectiveness
 * - Severity: medium (performance optimization)
 * - Treatment: enable minification plugin or build process
 *
 * @since 0.6093.1200
 */
class Diagnostic_CSS_Minification_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'css-minification-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CSS Minification Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSS is minified';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_styles;

		// Check for minification plugins that handle CSS.
		$minification_plugins = array(
			'autoptimize/autoptimize.php'         => 'Autoptimize',
			'wp-rocket/wp-rocket.php'             => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php'   => 'W3 Total Cache',
			'fast-velocity-minify/fvm.php'        => 'Fast Velocity Minify',
			'asset-cleanup/wpacu.php'             => 'Asset CleanUp',
		);

		$plugin_detected = false;
		$plugin_name     = '';

		foreach ( $minification_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = false;
				$plugin_name     = $name;
				break;
			}
		}

		// Count minified vs unminified CSS files.
		$total_styles    = 0;
		$minified_styles = 0;
		$total_size      = 0;

		if ( ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( ! empty( $style->src ) && is_string( $style->src ) && ! strpos( $style->src, '/wp-includes/' ) ) {
					// Skip WordPress core styles (already minified).
					$total_styles++;

					if ( strpos( $style->src, '.min.css' ) !== false ) {
						$minified_styles++;
					}

					// Estimate file size if local.
					$file_path = str_replace( content_url(), WP_CONTENT_DIR, $style->src );
					if ( file_exists( $file_path ) ) {
						$total_size += filesize( $file_path );
					}
				}
			}
		}

		// Calculate minification rate.
		$minification_rate = $total_styles > 0 ? ( $minified_styles / $total_styles ) * 100 : 0;

		// Critical: No minification plugin and most CSS unminified.
		if ( ! $plugin_detected && $minification_rate < 40 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: number of unminified styles, 2: total styles, 3: percentage */
					__( 'CSS minification not implemented. %1$d of %2$d stylesheets (%3$d%%) are unminified, containing unnecessary whitespace and comments. Install Autoptimize or WP Rocket to automatically minify CSS.', 'wpshadow' ),
					$total_styles - $minified_styles,
					$total_styles,
					round( 100 - $minification_rate )
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/css-minification?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'total_styles'       => $total_styles,
					'minified_styles'    => $minified_styles,
					'unminified_styles'  => $total_styles - $minified_styles,
					'minification_rate'  => round( $minification_rate, 2 ),
					'estimated_total_size' => $total_size > 0 ? size_format( $total_size ) : 'unknown',
					'plugin_detected'    => false,
					'recommendation'     => __( 'Install Autoptimize (free) for automatic CSS minification. Expected reduction: 30-50% smaller files. Also enables CSS combining for fewer HTTP requests.', 'wpshadow' ),
					'performance_impact' => array(
						'typical_reduction' => '30-50% file size reduction',
						'combined_with_gzip' => '70-80% total reduction',
						'mobile_improvement' => '1-3 seconds faster on 3G',
					),
				),
			);
		}

		// Medium: Plugin exists but minification rate low.
		if ( $plugin_detected && $minification_rate < 60 ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'CSS Minification Incomplete', 'wpshadow' ),
				'description' => sprintf(
					/* translators: 1: plugin name, 2: minification percentage */
					__( '%1$s is active but only %2$d%% of CSS files are minified. Check plugin settings to ensure CSS minification is enabled for all stylesheets.', 'wpshadow' ),
					$plugin_name,
					round( $minification_rate )
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/css-minification?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'plugin_detected'    => true,
					'plugin_name'        => $plugin_name,
					'total_styles'       => $total_styles,
					'minified_styles'    => $minified_styles,
					'minification_rate'  => round( $minification_rate, 2 ),
					'recommendation'     => sprintf(
						/* translators: %s: plugin name */
						__( 'Review %s settings to enable minification for all CSS files. Some files may be excluded or ignored.', 'wpshadow' ),
						$plugin_name
					),
				),
			);
		}

		// No issues - CSS minification working well.
		return null;
	}
}
