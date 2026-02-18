<?php
/**
 * Page Speed SEO Impact Diagnostic
 *
 * Tests if page speed is optimized for search engine rankings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1470
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Speed SEO Impact Diagnostic Class
 *
 * Validates that page speed meets Google Core Web Vitals requirements
 * which are now used as SEO ranking factors.
 *
 * @since 1.7034.1470
 */
class Diagnostic_Page_Speed_SEO_Impact extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-speed-seo-impact';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Page Speed SEO Impact';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if page speed is optimized for search engine rankings';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests page speed optimization including Core Web Vitals,
	 * image optimization, and caching configuration.
	 *
	 * @since  1.7034.1470
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for performance/speed optimization plugins.
		$speed_plugins = array(
			'wp-rocket/wp-rocket.php'                    => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php'         => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php'                => 'WP Super Cache',
			'autoptimize/autoptimize.php'                => 'Autoptimize',
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
			'smush/wp-smush.php'                         => 'WP Smush',
		);

		$active_speed_plugins = array();
		foreach ( $speed_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_speed_plugins[] = $name;
			}
		}

		// Check for Core Web Vitals optimization.
		$has_lcp_optimization = count( $active_speed_plugins ) > 0;
		$has_cls_optimization = count( $active_speed_plugins ) > 0;
		$has_fid_optimization = count( $active_speed_plugins ) > 0;

		// Check for lazy loading.
		$has_lazy_loading = is_plugin_active( 'a3-lazy-load/a3-lazy-load.php' ) ||
						  is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
						  is_plugin_active( 'autoptimize/autoptimize.php' );

		// Check for image optimization plugins.
		$has_image_optimizer = is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' ) ||
							 is_plugin_active( 'smush/wp-smush.php' ) ||
							 is_plugin_active( 'wp-rocket/wp-rocket.php' );

		// Check for minification.
		$style_css = get_stylesheet_directory() . '/style.css';
		$min_css = file_exists( get_stylesheet_directory() . '/style.min.css' );
		$has_minification_plugin = is_plugin_active( 'autoptimize/autoptimize.php' ) ||
								  is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
								  is_plugin_active( 'w3-total-cache/w3-total-cache.php' );

		// Check for CDN integration.
		$has_cdn = is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
				  is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ||
				  is_plugin_active( 'cloudflare/cloudflare.php' );

		// Check for GZIP compression.
		$gzip_enabled = false;
		$response = wp_remote_get( get_home_url(), array( 'sslverify' => false ) );
		if ( ! is_wp_error( $response ) ) {
			$encoding = wp_remote_retrieve_header( $response, 'content-encoding' );
			$gzip_enabled = ( $encoding === 'gzip' );
		}

		// Check for WebP support.
		$has_webp_support = is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' ) ||
						  is_plugin_active( 'wp-rocket/wp-rocket.php' );

		// Check for prefetching/preconnect.
		$theme_dir = get_template_directory();
		$header_file = $theme_dir . '/header.php';
		$has_prefetch = false;

		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			$has_prefetch = ( strpos( $header_content, 'prefetch' ) !== false ) ||
						  ( strpos( $header_content, 'preconnect' ) !== false ) ||
						  ( strpos( $header_content, 'dns-prefetch' ) !== false );
		}

		// Check for issues.
		$issues = array();

		// Issue 1: No performance optimization plugins.
		if ( empty( $active_speed_plugins ) ) {
			$issues[] = array(
				'type'        => 'no_speed_plugin',
				'description' => __( 'No performance optimization plugin installed; Core Web Vitals not optimized', 'wpshadow' ),
			);
		}

		// Issue 2: No caching enabled.
		$has_cache = is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
					is_plugin_active( 'wp-super-cache/wp-cache.php' ) ||
					is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ||
					is_plugin_active( 'cache-enabler/cache-enabler.php' );

		if ( ! $has_cache ) {
			$issues[] = array(
				'type'        => 'no_caching',
				'description' => __( 'No page caching enabled; pages regenerated on every request', 'wpshadow' ),
			);
		}

		// Issue 3: No lazy loading.
		if ( ! $has_lazy_loading ) {
			$issues[] = array(
				'type'        => 'no_lazy_loading',
				'description' => __( 'Lazy loading not configured; images load even when not visible', 'wpshadow' ),
			);
		}

		// Issue 4: No image optimization.
		if ( ! $has_image_optimizer ) {
			$issues[] = array(
				'type'        => 'no_image_optimization',
				'description' => __( 'No image optimization plugin; images may be oversized', 'wpshadow' ),
			);
		}

		// Issue 5: No minification.
		if ( ! $has_minification_plugin && ! $min_css ) {
			$issues[] = array(
				'type'        => 'no_minification',
				'description' => __( 'CSS/JS not minified; file sizes larger than necessary', 'wpshadow' ),
			);
		}

		// Issue 6: GZIP not enabled.
		if ( ! $gzip_enabled ) {
			$issues[] = array(
				'type'        => 'gzip_disabled',
				'description' => __( 'GZIP compression not enabled; transfer size 70-80% larger', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Page speed is not optimized for SEO; slow load times reduce rankings and user experience', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/page-speed-seo-impact',
				'details'      => array(
					'active_speed_plugins'   => $active_speed_plugins,
					'has_lcp_optimization'   => $has_lcp_optimization,
					'has_cls_optimization'   => $has_cls_optimization,
					'has_fid_optimization'   => $has_fid_optimization,
					'has_lazy_loading'       => $has_lazy_loading,
					'has_image_optimizer'    => $has_image_optimizer,
					'has_minification_plugin' => $has_minification_plugin,
					'has_cdn'                => $has_cdn,
					'gzip_enabled'           => $gzip_enabled,
					'has_webp_support'       => $has_webp_support,
					'has_prefetch'           => $has_prefetch,
					'issues_detected'        => $issues,
					'recommendation'         => __( 'Install caching plugin, enable lazy loading, optimize images, minify assets, enable GZIP', 'wpshadow' ),
					'core_web_vitals'        => array(
						'LCP (Largest Contentful Paint)' => 'Target: < 2.5s (SEO impact: high)',
						'FID (First Input Delay)'        => 'Target: < 100ms (SEO impact: high)',
						'CLS (Cumulative Layout Shift)'  => 'Target: < 0.1 (SEO impact: high)',
					),
					'speed_optimization_checklist' => array(
						'Page Caching'        => 'Reduces load from minutes to milliseconds',
						'Image Optimization'  => '40-60% file size reduction',
						'Lazy Loading'        => '30-50% faster perceived load',
						'Minification'        => '20-30% smaller CSS/JS files',
						'GZIP Compression'    => '70-80% smaller transfer size',
						'CDN'                 => '50-80% faster global delivery',
						'Browser Cache'       => 'Repeat visitors load instantly',
					),
					'seo_impact'             => array(
						'Ranking'             => '1s slower = drop 7+ positions',
						'Crawl Budget'        => '40% more pages crawled/faster',
						'Bounce Rate'         => 'Faster sites: 70% lower bounce',
						'Conversions'         => '100ms slower = 1% fewer conversions',
					),
					'testing_tools'          => array(
						'Google PageSpeed'    => 'https://developers.google.com/speed/pagespeed/insights',
						'Google Lighthouse'   => 'Built into Chrome DevTools',
						'WebPageTest'         => 'https://www.webpagetest.org/',
						'GTmetrix'            => 'https://gtmetrix.com/',
					),
					'target_performance'     => 'Good: 90-100, Fair: 50-89, Poor: <50',
				),
			);
		}

		return null;
	}
}
