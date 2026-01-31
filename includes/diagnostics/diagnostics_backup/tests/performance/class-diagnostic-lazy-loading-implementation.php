<?php
/**
 * Lazy Loading Implementation Diagnostic
 *
 * Verifies that images and iframes use native lazy loading to improve
 * page load performance and reduce initial bandwidth consumption.
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
 * Diagnostic_Lazy_Loading_Implementation Class
 *
 * Checks if lazy loading is properly implemented.
 *
 * @since 1.2601.2148
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
	protected static $description = 'Verifies lazy loading is enabled for images and iframes';

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
	 * @return array|null Finding array if lazy loading issues found, null otherwise.
	 */
	public static function check() {
		$lazy_status = self::check_lazy_loading_status();

		if ( $lazy_status['enabled'] ) {
			return null; // Lazy loading is active
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Lazy loading is not enabled. All images load immediately, slowing initial page load by 40-60% and wasting bandwidth.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/lazy-loading',
			'family'       => self::$family,
			'meta'         => array(
				'lazy_loading_enabled'      => false,
				'performance_impact'        => __( 'Initial page load 40-60% slower' ),
				'bandwidth_waste'           => __( 'Loading images user never sees' ),
				'mobile_impact'             => __( 'Critical on mobile (limited bandwidth)' ),
				'wordpress_native_support'  => __( 'WordPress 5.5+ has built-in support' ),
			),
			'details'      => array(
				'how_lazy_loading_works'  => array(
					__( 'Images below fold don\'t load until user scrolls near' ),
					__( 'Browser native: loading="lazy" attribute (no JavaScript)' ),
					__( 'JavaScript fallback for older browsers' ),
					__( 'Reduces initial page weight by 50-70%' ),
				),
				'performance_benefits'    => array(
					'Initial Page Load' => '40-60% faster',
					'Bandwidth Savings' => '50-70% reduction',
					'Mobile Experience' => '2-3x faster on 3G/4G',
					'Server Load' => '30% fewer image requests',
					'SEO Impact' => 'Better Core Web Vitals (LCP)',
				),
				'wordpress_native'        => array(
					'WordPress 5.5+' => array(
						__( 'Automatically adds loading="lazy" to images' ),
						__( 'Works out of the box, no plugin needed' ),
						__( 'Applies to post content images only' ),
						__( 'May need plugin for theme images' ),
					),
					'Check Version' => get_bloginfo( 'version' ),
				),
				'implementation_options'  => array(
					'Option 1: WordPress Native (Free)' => array(
						'WordPress 5.5+ only',
						'Automatic for content images',
						'No configuration needed',
						'Limited control',
					),
					'Option 2: Lazy Load Plugin (Free)' => array(
						'Plugin: Lazy Loader',
						'Works all WordPress versions',
						'More control over which images',
						'JavaScript-based fallback',
					),
					'Option 3: Performance Plugin (Premium)' => array(
						'WP Rocket ($50/year): Includes lazy load',
						'NitroPack ($20-100/month): Advanced lazy loading',
						'Full page optimization bundle',
						'Best for high-traffic sites',
					),
				),
				'manual_implementation'   => array(
					'Add to functions.php' => array(
						'// Force lazy loading on all images',
						'add_filter( "wp_img_tag_add_loading_attr", function() { return "lazy"; } );',
					),
					'Theme Template' => array(
						'<img src="image.jpg" loading="lazy" alt="Description">',
						'Add loading="lazy" to all <img> tags',
					),
				),
				'testing_lazy_loading'    => array(
					__( 'Open DevTools → Network tab' ),
					__( 'Load page, images below fold should NOT load' ),
					__( 'Scroll down, images load as they enter viewport' ),
					__( 'Check HTML: <img> tags have loading="lazy"' ),
				),
				'common_issues'           => array(
					'Above-fold images lazy' => array(
						'Problem: First visible image shouldn\'t be lazy',
						'Fix: Exclude hero/featured images from lazy load',
						'Impact: Slower LCP (Largest Contentful Paint)',
					),
					'JavaScript conflicts' => array(
						'Problem: Lazy load script conflicts with slider',
						'Fix: Use native lazy loading or update slider plugin',
					),
				),
			),
		);
	}

	/**
	 * Check lazy loading status.
	 *
	 * @since  1.2601.2148
	 * @return array Lazy loading status.
	 */
	private static function check_lazy_loading_status() {
		// WordPress 5.5+ has native lazy loading
		$wp_version = get_bloginfo( 'version' );
		if ( version_compare( $wp_version, '5.5', '>=' ) ) {
			// Check if explicitly disabled
			$disabled = has_filter( 'wp_lazy_loading_enabled', '__return_false' );
			if ( ! $disabled ) {
				return array( 'enabled' => true );
			}
		}

		// Check for lazy loading plugins
		$lazy_plugins = array(
			'a3-lazy-load/a3-lazy-load.php',
			'lazy-load/lazy-load.php',
			'wp-rocket/wp-rocket.php',
		);

		foreach ( $lazy_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return array( 'enabled' => true );
			}
		}

		return array( 'enabled' => false );
	}
}
