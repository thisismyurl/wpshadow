<?php
/**
 * Image Lazy Loading Configuration Treatment
 *
 * Tests if lazy loading is properly configured for images on the frontend.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Lazy Loading Configuration Treatment Class
 *
 * Validates that lazy loading is enabled for images to improve page load
 * performance by deferring offscreen image loads.
 *
 * @since 1.7034.1000
 */
class Treatment_Image_Lazy_Loading_Configuration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-lazy-loading-configuration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Lazy Loading Configuration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if lazy loading is properly configured for frontend images';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress has lazy loading enabled and if images
	 * are configured with loading="lazy" attribute.
	 *
	 * @since  1.7034.1000
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check WordPress version (lazy loading added in 5.5).
		global $wp_version;
		$wp_supports_lazy = version_compare( $wp_version, '5.5', '>=' );

		// Check if the_content filter has lazy loading.
		$has_lazy_loading_filter = false;
		if ( function_exists( 'wp_img_tag_add_loading_attr' ) ) {
			$has_lazy_loading_filter = has_filter( 'wp_content_img_tag', 'wp_img_tag_add_loading_attr' );
		}

		// Check for lazy loading plugins.
		$lazy_loading_plugins = array();
		$plugin_checks = array(
			'a3-lazy-load/a3-lazy-load.php'              => 'A3 Lazy Load',
			'rocket-lazy-load/rocket-lazy-load.php'      => 'Rocket Lazy Load',
			'wp-rocket/wp-rocket.php'                    => 'WP Rocket',
			'autoptimize/autoptimize.php'                => 'Autoptimize',
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
		);

		foreach ( $plugin_checks as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$lazy_loading_plugins[] = $name;
			}
		}

		// Check image optimization plugin settings.
		$has_image_optimization = false;
		if ( is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' ) ) {
			$has_image_optimization = true;
		} elseif ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			$has_image_optimization = true;
		}

		// Test a simple page to check for lazy loading attributes.
		$test_markup = '<img src="test.jpg" alt="test" />';
		$filtered_markup = apply_filters( 'wp_content_img_tag', $test_markup, null, null );
		$has_loading_attr = strpos( $filtered_markup, 'loading=' ) !== false;

		// Get image usage statistics.
		global $wpdb;
		$posts_with_images = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta}
			 WHERE meta_key = '_wp_attachment_image_alt' OR meta_key = '_thumbnail_id'"
		);

		// Check theme featured image support.
		$theme_supports_thumbnails = current_theme_supports( 'post-thumbnails' );

		// Check for issues.
		$issues = array();

		// Issue 1: WordPress version too old for native lazy loading.
		if ( ! $wp_supports_lazy ) {
			$issues[] = array(
				'type'        => 'old_wordpress',
				'description' => sprintf(
					/* translators: %s: minimum WordPress version */
					__( 'WordPress %s or newer required for native lazy loading support', 'wpshadow' ),
					'5.5'
				),
			);
		}

		// Issue 2: No lazy loading implementation detected.
		if ( ! $has_lazy_loading_filter && empty( $lazy_loading_plugins ) ) {
			$issues[] = array(
				'type'        => 'no_lazy_loading',
				'description' => __( 'No lazy loading detected; images load immediately regardless of viewport', 'wpshadow' ),
			);
		}

		// Issue 3: Lazy loading filter not applied to content images.
		if ( $wp_supports_lazy && ! $has_lazy_loading_filter ) {
			$issues[] = array(
				'type'        => 'filter_not_applied',
				'description' => __( 'WordPress lazy loading filter is not applied to content images', 'wpshadow' ),
			);
		}

		// Issue 4: No image optimization despite lazy loading.
		if ( ( $has_lazy_loading_filter || ! empty( $lazy_loading_plugins ) ) && ! $has_image_optimization ) {
			$issues[] = array(
				'type'        => 'no_optimization',
				'description' => __( 'Lazy loading enabled but no image optimization; images still too large', 'wpshadow' ),
			);
		}

		// Issue 5: Theme doesn't support featured images.
		if ( ! $theme_supports_thumbnails && absint( $posts_with_images ) > 0 ) {
			$issues[] = array(
				'type'        => 'theme_no_thumbnails',
				'description' => __( 'Theme does not support featured images but site uses them', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Image lazy loading is not properly configured, which may slow down initial page load times', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-lazy-loading-configuration',
				'details'      => array(
					'wp_version'               => $wp_version,
					'wp_supports_lazy'         => $wp_supports_lazy,
					'has_lazy_loading_filter'  => $has_lazy_loading_filter,
					'has_loading_attr'         => $has_loading_attr,
					'lazy_loading_plugins'     => $lazy_loading_plugins,
					'has_image_optimization'   => $has_image_optimization,
					'posts_with_images'        => absint( $posts_with_images ),
					'theme_supports_thumbnails' => $theme_supports_thumbnails,
					'issues_detected'          => $issues,
					'recommendation'           => __( 'Enable native WordPress lazy loading (WP 5.5+) or install image optimization plugin', 'wpshadow' ),
					'performance_impact'       => array(
						'without_lazy_loading' => '100% of images load on page load',
						'with_lazy_loading'    => 'Only visible images load; ~60-70% improvement in initial page load',
					),
					'testing_steps'            => array(
						__( '1. Inspect images in browser DevTools', 'wpshadow' ),
						__( '2. Check for loading="lazy" attribute', 'wpshadow' ),
						__( '3. Scroll page and verify below-fold images load on demand', 'wpshadow' ),
						__( '4. Check Network tab for defer loading pattern', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
