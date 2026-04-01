<?php
/**
 * Performance Testing Regular Diagnostic
 *
 * Tests if performance is monitored regularly through various
 * performance testing tools and monitoring systems.
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
 * Performance Testing Regular Diagnostic Class
 *
 * Evaluates whether the site has regular performance monitoring
 * and testing practices in place.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Performance_Testing_Regular extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tests-performance-regularly';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Testing Regular';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if performance is monitored regularly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the performance testing regular diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if performance testing issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for performance monitoring plugins.
		$monitoring_plugins = array(
			'query-monitor/query-monitor.php'                    => 'Query Monitor',
			'p3-profiler/p3-profiler.php'                        => 'P3 Plugin Performance Profiler',
			'new-relic-reporting/new-relic-reporting.php'        => 'New Relic',
			'wp-performance-profiler/wp-performance-profiler.php' => 'WP Performance Profiler',
			'debug-bar/debug-bar.php'                            => 'Debug Bar',
		);

		$active_monitoring_tools = array();
		foreach ( $monitoring_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_monitoring_tools[] = $name;
			}
		}

		$stats['monitoring_tools'] = $active_monitoring_tools;
		$stats['monitoring_tools_count'] = count( $active_monitoring_tools );

		// Check for performance optimization plugins.
		$optimization_plugins = array(
			'wp-rocket/wp-rocket.php'                          => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php'                => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php'                      => 'WP Super Cache',
			'autoptimize/autoptimize.php'                      => 'Autoptimize',
			'perfmatters/perfmatters.php'                      => 'Perfmatters',
			'wp-fastest-cache/wpFastestCache.php'              => 'WP Fastest Cache',
		);

		$active_optimization_tools = array();
		foreach ( $optimization_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_optimization_tools[] = $name;
			}
		}

		$stats['optimization_tools'] = $active_optimization_tools;
		$stats['optimization_tools_count'] = count( $active_optimization_tools );

		// Check for object caching.
		$has_object_cache = false;
		if ( wp_using_ext_object_cache() ) {
			$has_object_cache = true;
		}

		// Check for persistent object cache drop-in.
		$object_cache_file = WP_CONTENT_DIR . '/object-cache.php';
		if ( file_exists( $object_cache_file ) ) {
			$has_object_cache = true;
		}

		$stats['has_object_cache'] = $has_object_cache;

		// Check for page cache.
		$has_page_cache = false;
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			$has_page_cache = true;
		}

		$advanced_cache_file = WP_CONTENT_DIR . '/advanced-cache.php';
		if ( file_exists( $advanced_cache_file ) ) {
			$has_page_cache = true;
		}

		$stats['has_page_cache'] = $has_page_cache;

		// Check for CDN integration.
		$has_cdn = false;

		// Check for CDN plugins.
		$cdn_plugins = array(
			'cdn-enabler/cdn-enabler.php',
			'wp-rocket/wp-rocket.php',
			'w3-total-cache/w3-total-cache.php',
		);

		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cdn = true;
				break;
			}
		}

		// Check if site uses common CDN domains.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			$cdn_patterns = array(
				'cloudflare',
				'cloudfront',
				'fastly',
				'maxcdn',
				'stackpath',
				'bunnycdn',
				'keycdn',
				'cdn\.jsdelivr',
			);

			foreach ( $cdn_patterns as $pattern ) {
				if ( preg_match( '/' . $pattern . '/i', $html ) ) {
					$has_cdn = true;
					break;
				}
			}
		}

		$stats['has_cdn'] = $has_cdn;

		// Check for image optimization.
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

		// Check for database optimization.
		$database_optimization_plugins = array(
			'wp-optimize/wp-optimize.php'                    => 'WP-Optimize',
			'wp-sweep/wp-sweep.php'                          => 'WP-Sweep',
			'advanced-database-cleaner/advanced-db-cleaner.php' => 'Advanced Database Cleaner',
		);

		$has_database_optimization = false;
		foreach ( $database_optimization_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_database_optimization = true;
				break;
			}
		}

		$stats['has_database_optimization'] = $has_database_optimization;

		// Check for lazy loading.
		$has_lazy_loading = false;

		// WordPress 5.5+ has native lazy loading.
		$wp_version = get_bloginfo( 'version' );
		if ( version_compare( $wp_version, '5.5', '>=' ) ) {
			$has_lazy_loading = true;
		}

		// Check for lazy loading plugins.
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

		// Check for minification.
		$has_minification = false;
		if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ||
			 is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
			 is_plugin_active( 'fast-velocity-minify/fvm.php' ) ||
			 is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			$has_minification = true;
		}

		$stats['has_minification'] = $has_minification;

		// Check for WP_DEBUG (should be off in production).
		$debug_enabled = ( defined( 'WP_DEBUG' ) && WP_DEBUG );
		$stats['debug_enabled'] = $debug_enabled;

		if ( $debug_enabled ) {
			$warnings[] = __( 'WP_DEBUG is enabled - disable in production for better performance', 'wpshadow' );
		}

		// Calculate performance monitoring score.
		$performance_features = 0;
		$total_features = 8;

		if ( $stats['monitoring_tools_count'] > 0 ) { $performance_features++; }
		if ( $has_object_cache ) { $performance_features++; }
		if ( $has_page_cache ) { $performance_features++; }
		if ( $has_cdn ) { $performance_features++; }
		if ( $has_image_optimization ) { $performance_features++; }
		if ( $has_lazy_loading ) { $performance_features++; }
		if ( $has_minification ) { $performance_features++; }
		if ( $has_database_optimization ) { $performance_features++; }

		$stats['performance_monitoring_score'] = round( ( $performance_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( $stats['monitoring_tools_count'] === 0 ) {
			$issues[] = __( 'No performance monitoring tools active - install Query Monitor or similar', 'wpshadow' );
		}

		if ( ! $has_page_cache ) {
			$issues[] = __( 'No page caching active - critical for performance', 'wpshadow' );
		}

		if ( ! $has_object_cache ) {
			$warnings[] = __( 'No object caching active - consider Redis or Memcached for better performance', 'wpshadow' );
		}

		if ( ! $has_cdn ) {
			$warnings[] = __( 'No CDN detected - consider using Cloudflare or similar for global performance', 'wpshadow' );
		}

		if ( ! $has_image_optimization ) {
			$warnings[] = __( 'No image optimization active - large images slow down site', 'wpshadow' );
		}

		if ( ! $has_minification ) {
			$warnings[] = __( 'No CSS/JS minification active - reduces file sizes and improves load time', 'wpshadow' );
		}

		if ( ! $has_lazy_loading ) {
			$warnings[] = __( 'Lazy loading not active - delays image loading for faster initial page load', 'wpshadow' );
		}

		if ( $stats['optimization_tools_count'] === 0 ) {
			$warnings[] = __( 'No performance optimization plugins active - consider WP Rocket, W3 Total Cache, or Autoptimize', 'wpshadow' );
		}

		if ( $stats['performance_monitoring_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'Performance monitoring score is low (%s%%) - implement more optimization features', 'wpshadow' ),
				$stats['performance_monitoring_score']
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Performance testing/monitoring has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-testing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
				'description'  => __( 'Performance testing/monitoring has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-testing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Performance testing/monitoring is well implemented.
	}
}
