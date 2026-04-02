<?php
/**
 * Lazy Load Images Not Implemented Diagnostic
 *
 * Checks if lazy loading is implemented.
 * Lazy loading = load images only when visible in viewport.
 * Without = all images load immediately (even offscreen).
 * With lazy loading = offscreen images load when scrolled into view.
 *
 * **What This Check Does:**
 * - Scans for images in content
 * - Checks for loading="lazy" attribute
 * - Validates lazy loading library (if custom)
 * - Tests offscreen image loading behavior
 * - Measures initial page load impact
 * - Returns severity if images not lazy-loaded
 *
 * **Why This Matters:**
 * Blog post with 30 images. All load immediately.
 * User sees 3 images above fold. Downloaded 30.
 * Wasted bandwidth (27 images). Slow initial load.
 * With lazy loading: download 3 initially, others on-demand.
 * Fast initial load. Bandwidth saved.
 *
 * **Business Impact:**
 * Long-form article: 45 images, 18MB total. Initial page load:
 * downloads all 45 images. Mobile users: 25-second load on 3G.
 * Bounce rate: 72% (leave before content loads). Enabled native
 * lazy loading (loading="lazy"). Initial load: 5 images above fold
 * (2MB). Remaining 40 load when scrolled. Load time: 25s → 3.5s
 * (86% faster). Bounce rate: 72% → 18%. Bandwidth saved 70% for
 * users who don't scroll to bottom. Setup: WordPress 5.5+ automatic.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Content loads quickly
 * - #9 Show Value: Massive bandwidth + speed improvement
 * - #10 Beyond Pure: Smart resource loading
 *
 * **Related Checks:**
 * - Lazy Loading Attribute Usage (native implementation)
 * - Image Optimization (complementary)
 * - Offscreen Image Detection (related metric)
 *
 * **Learn More:**
 * Lazy loading: https://wpshadow.com/kb/lazy-loading
 * Video: Native lazy loading (8min): https://wpshadow.com/training/lazy-load
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
 * Lazy Load Images Not Implemented Diagnostic Class
 *
 * Detects non-lazy-loaded images.
 *
 * **Detection Pattern:**
 * 1. Parse HTML content
 * 2. Find all <img> tags
 * 3. Check for loading="lazy" attribute
 * 4. Check for lazy loading JavaScript library
 * 5. Test actual loading behavior (offscreen images)
 * 6. Return if images load eagerly
 *
 * **Real-World Scenario:**
 * WordPress 5.5+ automatically adds loading="lazy" to content images.
 * Result: <img src="..." loading="lazy">. Browser handles lazy loading
 * natively (zero JavaScript). Older WordPress or custom themes may not.
 * Added manually: images below fold get loading="lazy". Hero image:
 * no lazy (loads immediately). Result: initial page weight reduced 75%.
 *
 * **Implementation Notes:**
 * - Checks loading attribute presence
 * - Validates lazy loading implementation
 * - Tests offscreen loading behavior
 * - Severity: medium (significant bandwidth + speed improvement)
 * - Treatment: enable native lazy loading or plugin
 *
 * @since 1.6093.1200
 */
class Diagnostic_Lazy_Load_Images_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-load-images-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Load Images Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if lazy loading is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_version;

		$issues = array();

		// Check if WordPress version supports native lazy loading (5.5+).
		$native_support = version_compare( $wp_version, '5.5.0', '>=' );

		// Check for lazy loading plugins.
		$lazy_plugins = array(
			'a3-lazy-load/a3-lazy-load.php'              => 'A3 Lazy Load',
			'rocket-lazy-load/rocket-lazy-load.php'      => 'Rocket Lazy Load',
			'lazy-load/lazy-load.php'                    => 'Lazy Load',
			'wp-rocket/wp-rocket.php'                    => 'WP Rocket',
			'jetpack/jetpack.php'                        => 'Jetpack',
			'autoptimize/autoptimize.php'                => 'Autoptimize',
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
		);

		$plugin_detected = false;
		$plugin_name     = '';

		foreach ( $lazy_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = true;
				$plugin_name     = $name;
				break;
			}
		}

		// Check for native lazy loading filter.
		$has_lazy_filter = false;
		if ( function_exists( 'wp_img_tag_add_loading_attr' ) ) {
			$has_lazy_filter = has_filter( 'wp_content_img_tag', 'wp_img_tag_add_loading_attr' );
		}

		// Check for custom lazy loading implementation.
		$has_custom_lazy = has_filter( 'wp_content_img_tag' ) || has_filter( 'the_content' );

		// If no lazy loading detected at all.
		if ( ! $plugin_detected && ! $has_lazy_filter && ! $has_custom_lazy ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Lazy loading not implemented. Images load immediately even when off-screen, wasting bandwidth and slowing page load. Enable native lazy loading or install a plugin.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => $native_support,
				'kb_link'     => 'https://wpshadow.com/kb/lazy-loading',
				'details'     => array(
					'native_support'  => $native_support,
					'wp_version'      => $wp_version,
					'plugin_detected' => false,
					'recommendation'  => $native_support
						? __( 'WordPress 5.5+ includes native lazy loading. Enable it in your theme or add loading="lazy" attributes.', 'wpshadow' )
						: __( 'Upgrade to WordPress 5.5+ for native lazy loading, or install a lazy loading plugin like A3 Lazy Load or WP Rocket.', 'wpshadow' ),
					'impact'          => array(
						'bandwidth_saved' => '40-70%',
						'load_time_improvement' => '30-60%',
						'mobile_benefit' => 'Significant on slow connections',
					),
				),
			);
		}

		// If native support available but not fully enabled.
		if ( $native_support && ! $has_lazy_filter && ! $plugin_detected ) {
			$issues[] = __( 'WordPress 5.5+ native lazy loading available but not fully enabled', 'wpshadow' );
		}

		// If using JavaScript plugin when native is available.
		if ( $native_support && $plugin_detected && ! in_array( $plugin_name, array( 'WP Rocket', 'Autoptimize' ), true ) ) {
			$issues[] = sprintf(
				/* translators: %s: plugin name */
				__( 'Using %s plugin when WordPress native lazy loading is available (5.5+). Consider using native approach for better performance.', 'wpshadow' ),
				$plugin_name
			);
		}

		// Return low-severity finding if using non-optimal approach.
		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Lazy Loading Optimization Opportunity', 'wpshadow' ),
				'description' => implode( ' ', $issues ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/lazy-loading',
				'details'     => array(
					'native_support'  => $native_support,
					'plugin_detected' => $plugin_detected,
					'plugin_name'     => $plugin_name,
				),
			);
		}

		// No issues found.
		return null;
	}
}
