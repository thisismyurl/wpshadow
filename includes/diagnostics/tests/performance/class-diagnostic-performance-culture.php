<?php
/**
 * Performance Culture Diagnostic
 *
 * Tests if team treats performance as a priority through
 * monitoring, documentation, and optimization practices.
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
 * Performance Culture Diagnostic Class
 *
 * Evaluates whether the site demonstrates organizational
 * commitment to performance optimization.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Performance_Culture extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'builds-performance-culture';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Culture';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if team treats performance as priority';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the performance culture diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if performance culture issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for performance monitoring infrastructure.
		$monitoring_plugins = array(
			'query-monitor/query-monitor.php'              => 'Query Monitor',
			'debug-bar/debug-bar.php'                      => 'Debug Bar',
			'new-relic-reporting/wp-new-relic.php'         => 'New Relic',
		);

		$has_monitoring = false;
		foreach ( $monitoring_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_monitoring = true;
				break;
			}
		}

		$stats['has_monitoring'] = $has_monitoring;

		// Check for multiple performance optimization plugins (shows commitment).
		$performance_plugins = array(
			'wp-rocket/wp-rocket.php'                      => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php'            => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php'                  => 'WP Super Cache',
			'autoptimize/autoptimize.php'                  => 'Autoptimize',
			'perfmatters/perfmatters.php'                  => 'Perfmatters',
			'litespeed-cache/litespeed-cache.php'          => 'LiteSpeed Cache',
		);

		$active_performance_plugins = array();
		foreach ( $performance_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_performance_plugins[] = $name;
			}
		}

		$stats['active_performance_plugins'] = $active_performance_plugins;
		$stats['performance_plugin_count'] = count( $active_performance_plugins );

		// Check for WPShadow performance settings.
		$wpshadow_performance_budget = get_option( 'wpshadow_performance_budget', null );
		$has_performance_budget = ! empty( $wpshadow_performance_budget );
		$stats['has_performance_budget'] = $has_performance_budget;

		// Check for caching strategy.
		$has_page_cache = false;
		$has_object_cache = wp_using_ext_object_cache();
		$has_browser_cache = false;

		foreach ( $performance_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_page_cache = true;
				break;
			}
		}

		// Check for CDN.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		$has_cdn = false;
		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
			if ( isset( $headers['cf-ray'] ) || isset( $headers['x-cdn'] ) || isset( $headers['x-cache'] ) ) {
				$has_cdn = true;
			}
		}

		// Check for browser cache headers.
		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
			if ( isset( $headers['cache-control'] ) || isset( $headers['expires'] ) ) {
				$has_browser_cache = true;
			}
		}

		$stats['has_page_cache'] = $has_page_cache;
		$stats['has_object_cache'] = $has_object_cache;
		$stats['has_browser_cache'] = $has_browser_cache;
		$stats['has_cdn'] = $has_cdn;

		// Check for image optimization.
		$image_optimization_plugins = array(
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'imagify/imagify.php',
			'smush/smush.php',
		);

		$has_image_optimization = false;
		foreach ( $image_optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_image_optimization = true;
				break;
			}
		}

		$stats['has_image_optimization'] = $has_image_optimization;

		// Check for database optimization.
		$db_optimization_plugins = array(
			'wp-optimize/wp-optimize.php',
			'advanced-database-cleaner/advanced-db-cleaner.php',
		);

		$has_db_optimization = false;
		foreach ( $db_optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_db_optimization = true;
				break;
			}
		}

		$stats['has_db_optimization'] = $has_db_optimization;

		// Check for minification.
		$minification_active = false;
		if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ||
			 is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
			 is_plugin_active( 'fast-velocity-minify/fvm.php' ) ) {
			$minification_active = true;
		}

		$stats['has_minification'] = $minification_active;

		// Check for lazy loading.
		$has_lazy_loading = false;
		$wp_version = get_bloginfo( 'version' );
		if ( version_compare( $wp_version, '5.5', '>=' ) ) {
			$has_lazy_loading = true;
		}

		$lazy_load_plugins = array(
			'rocket-lazy-load/rocket-lazy-load.php',
			'a3-lazy-load/a3-lazy-load.php',
		);

		foreach ( $lazy_load_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_lazy_loading = true;
				break;
			}
		}

		$stats['has_lazy_loading'] = $has_lazy_loading;

		// Check for documentation (performance page/docs).
		$performance_pages = get_posts( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'numberposts' => 1,
			's'           => 'performance',
		) );

		$has_performance_docs = ! empty( $performance_pages );
		$stats['has_performance_docs'] = $has_performance_docs;

		// Check plugin count (too many plugins can indicate lack of performance awareness).
		$all_plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$total_active_plugins = count( $active_plugins );
		$total_plugins = count( $all_plugins );

		$stats['total_active_plugins'] = $total_active_plugins;
		$stats['total_plugins'] = $total_plugins;

		// Check for unused plugins (shows attention to performance).
		$unused_plugins = $total_plugins - $total_active_plugins;
		$stats['unused_plugins'] = $unused_plugins;

		// Check theme performance indicators.
		$theme = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();

		$has_build_tools = file_exists( $theme_dir . '/webpack.config.js' ) ||
						   file_exists( $theme_dir . '/vite.config.js' ) ||
						   file_exists( $theme_dir . '/gulpfile.js' );

		$stats['has_build_tools'] = $has_build_tools;

		// Calculate performance culture score.
		$culture_features = 0;
		$total_features = 12;

		if ( $has_monitoring ) { $culture_features++; }
		if ( $stats['performance_plugin_count'] >= 2 ) { $culture_features++; }
		if ( $has_performance_budget ) { $culture_features++; }
		if ( $has_page_cache ) { $culture_features++; }
		if ( $has_object_cache ) { $culture_features++; }
		if ( $has_cdn ) { $culture_features++; }
		if ( $has_image_optimization ) { $culture_features++; }
		if ( $has_db_optimization ) { $culture_features++; }
		if ( $minification_active ) { $culture_features++; }
		if ( $has_lazy_loading ) { $culture_features++; }
		if ( $total_active_plugins < 30 ) { $culture_features++; }
		if ( $has_build_tools ) { $culture_features++; }

		$stats['performance_culture_score'] = round( ( $culture_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( ! $has_monitoring ) {
			$issues[] = __( 'No performance monitoring active - install Query Monitor or New Relic', 'wpshadow' );
		}

		if ( $stats['performance_plugin_count'] === 0 ) {
			$issues[] = __( 'No performance optimization plugins active - shows lack of performance focus', 'wpshadow' );
		}

		if ( ! $has_performance_budget ) {
			$warnings[] = __( 'No performance budget set - define performance targets in WPShadow settings', 'wpshadow' );
		}

		if ( ! $has_page_cache ) {
			$issues[] = __( 'No page caching active - critical for performance', 'wpshadow' );
		}

		if ( ! $has_object_cache ) {
			$warnings[] = __( 'Object cache not enabled - consider Redis or Memcached', 'wpshadow' );
		}

		if ( ! $has_cdn ) {
			$warnings[] = __( 'No CDN detected - consider Cloudflare for global performance', 'wpshadow' );
		}

		if ( ! $has_image_optimization ) {
			$warnings[] = __( 'No image optimization - images are often the biggest performance bottleneck', 'wpshadow' );
		}

		if ( ! $has_db_optimization ) {
			$warnings[] = __( 'No database optimization scheduled - database bloat affects performance', 'wpshadow' );
		}

		if ( ! $minification_active ) {
			$warnings[] = __( 'No minification active - reduces file sizes significantly', 'wpshadow' );
		}

		if ( ! $has_lazy_loading ) {
			$warnings[] = __( 'Lazy loading not enabled - improves initial page load', 'wpshadow' );
		}

		if ( $total_active_plugins > 30 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of plugins */
				__( '%d active plugins - consider reducing for better performance', 'wpshadow' ),
				$total_active_plugins
			);
		}

		if ( ! $has_build_tools ) {
			$warnings[] = __( 'No build tools in theme - modern build tools optimize performance', 'wpshadow' );
		}

		if ( $stats['performance_culture_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'Performance culture score is low (%s%%) - team should prioritize performance', 'wpshadow' ),
				$stats['performance_culture_score']
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Performance culture has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-culture?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
				'description'  => __( 'Performance culture has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-culture?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Performance culture is strong.
	}
}
