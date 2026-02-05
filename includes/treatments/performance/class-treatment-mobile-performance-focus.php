<?php
/**
 * Mobile Performance Focus Treatment
 *
 * Tests if mobile performance is prioritized through responsive design,
 * mobile-specific optimizations, and mobile user experience features.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Performance Focus Treatment Class
 *
 * Evaluates whether the site is optimized for mobile devices
 * including responsive design, mobile speed, and mobile UX.
 *
 * @since 1.6035.1400
 */
class Treatment_Mobile_Performance_Focus extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'optimizes-mobile-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Performance Focus';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if mobile performance is prioritized';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the mobile performance focus treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if mobile performance issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check if theme is responsive.
		$theme = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();
		$style_file = $theme_dir . '/style.css';

		$is_responsive = false;
		if ( file_exists( $style_file ) ) {
			$style_content = file_get_contents( $style_file );
			
			// Check for media queries (sign of responsive design).
			if ( preg_match( '/@media.*\(.*max-width|@media.*\(.*min-width/i', $style_content ) ) {
				$is_responsive = true;
			}
		}

		$stats['is_responsive'] = $is_responsive;

		// Check viewport meta tag.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		$has_viewport_meta = false;
		$has_mobile_optimized_meta = false;

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			// Check for viewport meta tag.
			if ( preg_match( '/<meta[^>]*name=["\']viewport["\'][^>]*>/i', $html ) ) {
				$has_viewport_meta = true;
			}

			// Check for mobile-optimized meta.
			if ( preg_match( '/content=["\']width=device-width/i', $html ) ) {
				$has_mobile_optimized_meta = true;
			}
		}

		$stats['has_viewport_meta'] = $has_viewport_meta;
		$stats['has_mobile_optimized_meta'] = $has_mobile_optimized_meta;

		// Check for mobile-specific plugins.
		$mobile_plugins = array(
			'wp-mobile-detect/wp-mobile-detect.php'      => 'WP Mobile Detect',
			'jetpack/jetpack.php'                        => 'Jetpack (has mobile theme)',
			'amp/amp.php'                                => 'AMP (Accelerated Mobile Pages)',
		);

		$active_mobile_plugins = array();
		foreach ( $mobile_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_mobile_plugins[] = $name;
			}
		}

		$stats['mobile_plugins'] = $active_mobile_plugins;
		$stats['mobile_plugins_count'] = count( $active_mobile_plugins );

		// Check for AMP support.
		$has_amp = is_plugin_active( 'amp/amp.php' );
		$stats['has_amp'] = $has_amp;

		// Check for lazy loading (helps mobile performance).
		$has_lazy_loading = false;
		
		$wp_version = get_bloginfo( 'version' );
		if ( version_compare( $wp_version, '5.5', '>=' ) ) {
			$has_lazy_loading = true;
		}

		$lazy_load_plugins = array(
			'rocket-lazy-load/rocket-lazy-load.php',
			'a3-lazy-load/a3-lazy-load.php',
			'lazy-load/lazy-load.php',
		);

		foreach ( $lazy_load_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_lazy_loading = true;
				break;
			}
		}

		$stats['has_lazy_loading'] = $has_lazy_loading;

		// Check for image optimization (critical for mobile).
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

		$stats['has_image_optimization'] = $has_image_optimization;

		// Check for mobile caching.
		$caching_plugins = array(
			'wp-rocket/wp-rocket.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
		);

		$has_caching = false;
		foreach ( $caching_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_caching = true;
				break;
			}
		}

		$stats['has_caching'] = $has_caching;

		// Check for touch-friendly features.
		$has_touch_friendly_menu = false;
		if ( file_exists( $style_file ) ) {
			$style_content = file_get_contents( $style_file );
			
			// Look for mobile menu indicators.
			if ( preg_match( '/\.mobile-menu|\.hamburger|\.nav-toggle/i', $style_content ) ) {
				$has_touch_friendly_menu = true;
			}
		}

		// Check theme functions for mobile menu.
		$functions_file = $theme_dir . '/functions.php';
		if ( ! $has_touch_friendly_menu && file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );
			if ( preg_match( '/mobile.*menu|hamburger|nav.*toggle/i', $functions_content ) ) {
				$has_touch_friendly_menu = true;
			}
		}

		$stats['has_touch_friendly_menu'] = $has_touch_friendly_menu;

		// Check for font size optimization.
		$has_readable_fonts = false;
		if ( file_exists( $style_file ) ) {
			$style_content = file_get_contents( $style_file );
			
			// Check for responsive font sizing.
			if ( preg_match( '/font-size.*clamp|font-size.*vw|font-size.*rem/i', $style_content ) ) {
				$has_readable_fonts = true;
			}
		}

		$stats['has_readable_fonts'] = $has_readable_fonts;

		// Check for minification (helps mobile performance).
		$has_minification = false;
		if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ||
			 is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
			 is_plugin_active( 'fast-velocity-minify/fvm.php' ) ) {
			$has_minification = true;
		}

		$stats['has_minification'] = $has_minification;

		// Check for PWA support.
		$pwa_plugins = array(
			'pwa/pwa.php',
			'super-progressive-web-apps/superpwa.php',
		);

		$has_pwa = false;
		foreach ( $pwa_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_pwa = true;
				break;
			}
		}

		$stats['has_pwa'] = $has_pwa;

		// Calculate mobile optimization score.
		$mobile_features = 0;
		$total_features = 10;

		if ( $is_responsive ) { $mobile_features++; }
		if ( $has_viewport_meta ) { $mobile_features++; }
		if ( $has_lazy_loading ) { $mobile_features++; }
		if ( $has_image_optimization ) { $mobile_features++; }
		if ( $has_caching ) { $mobile_features++; }
		if ( $has_touch_friendly_menu ) { $mobile_features++; }
		if ( $has_minification ) { $mobile_features++; }
		if ( $has_mobile_optimized_meta ) { $mobile_features++; }
		if ( $has_amp ) { $mobile_features++; }
		if ( $has_pwa ) { $mobile_features++; }

		$stats['mobile_optimization_score'] = round( ( $mobile_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( ! $is_responsive ) {
			$issues[] = __( 'Theme does not appear to be responsive - critical for mobile users', 'wpshadow' );
		}

		if ( ! $has_viewport_meta ) {
			$issues[] = __( 'Missing viewport meta tag - mobile browsers will not scale correctly', 'wpshadow' );
		}

		if ( ! $has_caching ) {
			$warnings[] = __( 'No caching plugin active - mobile users on slow connections will suffer', 'wpshadow' );
		}

		if ( ! $has_lazy_loading ) {
			$warnings[] = __( 'Lazy loading not enabled - mobile data usage and speed affected', 'wpshadow' );
		}

		if ( ! $has_image_optimization ) {
			$warnings[] = __( 'No image optimization - large images impact mobile performance', 'wpshadow' );
		}

		if ( ! $has_touch_friendly_menu ) {
			$warnings[] = __( 'No touch-friendly mobile menu detected - consider hamburger menu', 'wpshadow' );
		}

		if ( ! $has_minification ) {
			$warnings[] = __( 'No minification active - reduces mobile data usage', 'wpshadow' );
		}

		if ( ! $has_amp ) {
			$warnings[] = __( 'AMP not enabled - consider for ultra-fast mobile pages', 'wpshadow' );
		}

		if ( ! $has_pwa ) {
			$warnings[] = __( 'No PWA support - consider for app-like mobile experience', 'wpshadow' );
		}

		if ( $stats['mobile_optimization_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'Mobile optimization score is low (%s%%) - improve mobile experience', 'wpshadow' ),
				$stats['mobile_optimization_score']
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Mobile performance optimization has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-performance',
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
				'description'  => __( 'Mobile performance has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-performance',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Mobile performance is well optimized.
	}
}
