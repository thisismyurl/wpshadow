<?php
/**
 * Core Web Vitals Prioritized Diagnostic
 *
 * Tests focus on Largest Contentful Paint (LCP), First Input Delay (FID),
 * and Cumulative Layout Shift (CLS) scores.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core Web Vitals Diagnostic Class
 *
 * Evaluates whether the site is optimized for Google's Core Web Vitals:
 * LCP, FID, and CLS. Checks for performance monitoring and optimization.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Core_Web_Vitals extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'prioritizes-core-web-vitals';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core Web Vitals Prioritized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests focus on LCP, FID, and CLS scores';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the Core Web Vitals diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if Core Web Vitals issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for performance monitoring plugins.
		$performance_plugins = array(
			'query-monitor/query-monitor.php'                  => 'Query Monitor',
			'wp-performance-score-booster/wp-performance-score-booster.php' => 'WP Performance Score Booster',
			'perfmatters/perfmatters.php'                      => 'Perfmatters',
			'wp-rocket/wp-rocket.php'                          => 'WP Rocket',
			'autoptimize/autoptimize.php'                      => 'Autoptimize',
			'w3-total-cache/w3-total-cache.php'                => 'W3 Total Cache',
			'wp-fastest-cache/wpFastestCache.php'              => 'WP Fastest Cache',
		);

		$active_performance_plugin = null;
		foreach ( $performance_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_performance_plugin = $name;
				break;
			}
		}

		$stats['performance_plugin'] = $active_performance_plugin;

		// Check for lazy loading (helps LCP).
		$lazy_loading_enabled = false;
		
		// WordPress 5.5+ has native lazy loading.
		$wp_version = get_bloginfo( 'version' );
		if ( version_compare( $wp_version, '5.5', '>=' ) ) {
			$lazy_loading_enabled = true;
		}

		// Check for lazy loading plugins.
		$lazy_load_plugins = array(
			'rocket-lazy-load/rocket-lazy-load.php',
			'a3-lazy-load/a3-lazy-load.php',
			'lazy-load/lazy-load.php',
		);

		foreach ( $lazy_load_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$lazy_loading_enabled = true;
				break;
			}
		}

		$stats['lazy_loading_enabled'] = $lazy_loading_enabled;

		// Check for image optimization (affects LCP).
		$image_optimization_plugins = array(
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
			'shortpixel-image-optimiser/wp-shortpixel.php'  => 'ShortPixel',
			'imagify/imagify.php'                           => 'Imagify',
			'smush/smush.php'                               => 'Smush',
		);

		$has_image_optimization = false;
		foreach ( $image_optimization_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_image_optimization = true;
				break;
			}
		}

		$stats['image_optimization'] = $has_image_optimization;

		// Check for caching (affects all vitals).
		$caching_plugins = array(
			'wp-rocket/wp-rocket.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'cache-enabler/cache-enabler.php',
			'comet-cache/comet-cache.php',
		);

		$has_caching = false;
		foreach ( $caching_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_caching = true;
				break;
			}
		}

		$stats['has_caching'] = $has_caching;

		// Check for minification (affects LCP and FID).
		$minification_active = false;
		
		if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ||
			 is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
			 is_plugin_active( 'fast-velocity-minify/fvm.php' ) ) {
			$minification_active = true;
		}

		$stats['minification_active'] = $minification_active;

		// Check for preloading (helps LCP).
		$theme = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';
		$header_file = $theme_dir . '/header.php';

		$has_preload = false;
		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );
			if ( strpos( $functions_content, 'rel="preload"' ) !== false ||
				 strpos( $functions_content, 'rel="prefetch"' ) !== false ||
				 strpos( $functions_content, 'rel="dns-prefetch"' ) !== false ) {
				$has_preload = true;
			}
		}

		if ( ! $has_preload && file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			if ( strpos( $header_content, 'rel="preload"' ) !== false ||
				 strpos( $header_content, 'rel="prefetch"' ) !== false ||
				 strpos( $header_content, 'rel="dns-prefetch"' ) !== false ) {
				$has_preload = true;
			}
		}

		$stats['has_preload'] = $has_preload;

		// Check for async/defer on scripts (helps FID).
		$has_async_defer = false;
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			if ( strpos( $header_content, 'async' ) !== false ||
				 strpos( $header_content, 'defer' ) !== false ) {
				$has_async_defer = true;
			}
		}

		// Check if using async/defer plugins.
		if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ||
			 is_plugin_active( 'async-javascript/async-javascript.php' ) ||
			 is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			$has_async_defer = true;
		}

		$stats['has_async_defer'] = $has_async_defer;

		// Check for font optimization (affects CLS).
		$has_font_optimization = false;
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			if ( strpos( $header_content, 'font-display' ) !== false ||
				 strpos( $header_content, 'preload' ) !== false && strpos( $header_content, 'font' ) !== false ) {
				$has_font_optimization = true;
			}
		}

		$stats['has_font_optimization'] = $has_font_optimization;

		// Check for width/height attributes on images (prevents CLS).
		$posts = get_posts( array(
			'posts_per_page' => 5,
			'post_type'      => 'post',
			'post_status'    => 'publish',
		) );

		$images_with_dimensions = 0;
		$images_without_dimensions = 0;

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			
			// Count images.
			preg_match_all( '/<img[^>]+>/i', $content, $img_matches );
			
			if ( ! empty( $img_matches[0] ) ) {
				foreach ( $img_matches[0] as $img_tag ) {
					if ( preg_match( '/width=["\']([0-9]+)["\']/', $img_tag ) &&
						 preg_match( '/height=["\']([0-9]+)["\']/', $img_tag ) ) {
						$images_with_dimensions++;
					} else {
						$images_without_dimensions++;
					}
				}
			}
		}

		$stats['images_with_dimensions'] = $images_with_dimensions;
		$stats['images_without_dimensions'] = $images_without_dimensions;

		$total_images = $images_with_dimensions + $images_without_dimensions;
		if ( $total_images > 0 ) {
			$stats['images_dimension_percentage'] = round( ( $images_with_dimensions / $total_images ) * 100, 1 );
		} else {
			$stats['images_dimension_percentage'] = 100;
		}

		// Evaluate issues.
		if ( ! $has_caching ) {
			$issues[] = __( 'No caching plugin active - impacts all Core Web Vitals', 'wpshadow' );
		}

		if ( ! $lazy_loading_enabled ) {
			$warnings[] = __( 'Lazy loading not enabled - affects LCP scores', 'wpshadow' );
		}

		if ( ! $has_image_optimization ) {
			$warnings[] = __( 'No image optimization plugin active - large images impact LCP', 'wpshadow' );
		}

		if ( ! $minification_active ) {
			$warnings[] = __( 'No minification active - affects LCP and FID', 'wpshadow' );
		}

		if ( ! $has_async_defer ) {
			$issues[] = __( 'Scripts not loaded async/defer - impacts FID (First Input Delay)', 'wpshadow' );
		}

		if ( ! $has_font_optimization ) {
			$warnings[] = __( 'Font loading not optimized - can cause layout shifts (CLS)', 'wpshadow' );
		}

		if ( $images_without_dimensions > $images_with_dimensions ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				__( '%d images missing width/height attributes - causes CLS (Cumulative Layout Shift)', 'wpshadow' ),
				$images_without_dimensions
			);
		}

		if ( ! $active_performance_plugin ) {
			$warnings[] = __( 'No performance monitoring plugin active - consider Query Monitor or similar', 'wpshadow' );
		}

		if ( ! $has_preload ) {
			$warnings[] = __( 'Resource preloading not detected - can improve LCP', 'wpshadow' );
		}

		// Count optimization score.
		$optimizations_active = 0;
		$optimizations_possible = 7;

		if ( $has_caching ) { $optimizations_active++; }
		if ( $lazy_loading_enabled ) { $optimizations_active++; }
		if ( $has_image_optimization ) { $optimizations_active++; }
		if ( $minification_active ) { $optimizations_active++; }
		if ( $has_async_defer ) { $optimizations_active++; }
		if ( $has_font_optimization ) { $optimizations_active++; }
		if ( $stats['images_dimension_percentage'] > 70 ) { $optimizations_active++; }

		$stats['cwv_optimization_score'] = round( ( $optimizations_active / $optimizations_possible ) * 100, 1 );

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Core Web Vitals optimization has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/core-web-vitals',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Core Web Vitals optimization has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/core-web-vitals',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Core Web Vitals are well optimized.
	}
}
