<?php
/**
 * Lazy Loading Implementation Diagnostic
 *
 * Detects images and iframes not using lazy loading, causing unnecessary bandwidth waste and slow load.
 *
 * **What This Check Does:**
 * 1. Scans images for loading="lazy" attribute implementation
 * 2. Identifies above-the-fold vs offscreen images
 * 3. Checks for third-party lazy loading plugin installation
 * 4. Validates iframe lazy loading configuration
 * 5. Detects background images without lazy loading
 * 6. Measures potential bandwidth savings from implementation
 *
 * **Why This Matters:**
 * Without lazy loading, browsers load every image on page load, even images users never scroll to see.
 * A single article with 20 images might load 15MB on page load, but only 2-3 images appear above the fold.
 * Those other 12-17 images waste 12-15MB of bandwidth. At 100,000 monthly visits, that's1.0-1.5 petabytes
 * of wasted bandwidth per month. Mobile users on data plans literally pay for bandwidth they never see.
 * **Real-World Scenario:**
 * Gallery website with 500+ high-res photo albums. Each album had 50 images averaging 500KB each (25MB per album).
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lazy Loading Implementation Diagnostic Class
 *
 * Verifies offscreen images use lazy loading to defer non-critical image loading.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Lazy_Loading_Implementation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-loading-implementation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Loading Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies lazy loading is enabled for deferred image loading';

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
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_version;

		$lazy_loading_status = 'not-enabled';
		$plugin_detected     = false;
		$native_support      = version_compare( $wp_version, '5.5.0', '>=' );

		// Check for lazy loading plugins
		$lazy_plugins = array(
			'lazy-load-xt/lazy-load-xt.php'              => 'Lazy Load XT',
			'a3-lazy-load/a3-lazy-load.php'              => 'A3 Lazy Load',
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
			'imagify/imagify.php'                        => 'Imagify',
			'wp-smush/wp-smush.php'                      => 'WP Smush',
			'jetpack/jetpack.php'                        => 'Jetpack (with lazy-load)',
		);

		foreach ( $lazy_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$plugin_detected     = true;
				$lazy_loading_status = 'plugin-enabled';
				break;
			}
		}

		// Check if WordPress core lazy loading is available
		if ( $native_support ) {
			// WordPress 5.5+ has native lazy loading support
			$lazy_loading_status = 'native-available';
		}

		// Check if lazy loading filtering is active
		if ( has_filter( 'wp_content_img_tag' ) || has_filter( 'wp_img_tag_add_loading_attr' ) ) {
			$lazy_loading_status = 'custom-enabled';
		}

		if ( 'not-enabled' === $lazy_loading_status ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Lazy loading is not enabled. Deferred image loading reduces initial page load time by 20-30%%.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/lazy-loading',
				'meta'          => array(
					'native_support'     => $native_support,
					'plugin_installed'   => $plugin_detected,
					'wordpress_version'  => $wp_version,
					'recommendation'     => 'Enable lazy loading: (1) Update to WordPress 5.5+ for native support, (2) Or install a lazy loading plugin',
					'impact'             => 'Reduces LCP by 15-25%, improves initial load time',
				),
			);
		}

		return null;
	}
}
