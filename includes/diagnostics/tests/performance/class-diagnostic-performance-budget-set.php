<?php
/**
 * Performance Budget Set Diagnostic
 *
 * Tests if team has defined performance targets and
 * monitoring infrastructure for tracking against budgets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1405
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Budget Set Diagnostic Class
 *
 * Evaluates whether the site has defined performance budgets
 * and monitoring to track compliance.
 *
 * @since 1.6035.1405
 */
class Diagnostic_Performance_Budget_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sets-performance-budget';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Budget Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if team has defined performance targets';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the performance budget set diagnostic check.
	 *
	 * @since  1.6035.1405
	 * @return array|null Finding array if performance budget issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for performance monitoring plugins.
		$monitoring_plugins = array(
			'query-monitor/query-monitor.php'              => 'Query Monitor',
			'debug-bar/debug-bar.php'                      => 'Debug Bar',
			'new-relic-reporting/wp-new-relic.php'         => 'New Relic',
		);

		$has_monitoring = false;
		$active_monitoring_tools = array();
		foreach ( $monitoring_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_monitoring = true;
				$active_monitoring_tools[] = $name;
			}
		}

		$stats['has_monitoring'] = $has_monitoring;
		$stats['active_monitoring_tools'] = $active_monitoring_tools;

		// Check for WPShadow performance budget option (our own feature).
		$wpshadow_budget = get_option( 'wpshadow_performance_budget', null );
		$has_wpshadow_budget = ! empty( $wpshadow_budget );
		$stats['has_wpshadow_budget'] = $has_wpshadow_budget;

		if ( $has_wpshadow_budget ) {
			$stats['wpshadow_budget'] = $wpshadow_budget;
		}

		// Check for performance plugin configurations that suggest budgets.
		$performance_plugins = array(
			'wp-rocket/wp-rocket.php'                      => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php'            => 'W3 Total Cache',
			'autoptimize/autoptimize.php'                  => 'Autoptimize',
			'perfmatters/perfmatters.php'                  => 'Perfmatters',
		);

		$active_performance_plugins = array();
		foreach ( $performance_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_performance_plugins[] = $name;
			}
		}

		$stats['active_performance_plugins'] = $active_performance_plugins;
		$stats['has_performance_optimization'] = ! empty( $active_performance_plugins );

		// Check for Google Analytics with page speed metrics.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		$has_analytics = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			if ( preg_match( '/gtag\(|ga\(|google-analytics\.com\/analytics\.js/i', $html ) ) {
				$has_analytics = true;
			}
		}

		$stats['has_analytics'] = $has_analytics;

		// Check for Site Kit (provides performance insights).
		$has_site_kit = is_plugin_active( 'google-site-kit/google-site-kit.php' );
		$stats['has_site_kit'] = $has_site_kit;

		// Check for caching (sign of performance focus).
		$caching_plugins = array(
			'wp-rocket/wp-rocket.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'litespeed-cache/litespeed-cache.php',
		);

		$has_caching = false;
		foreach ( $caching_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_caching = true;
				break;
			}
		}

		$stats['has_caching'] = $has_caching;

		// Check if object caching is enabled.
		$has_object_cache = wp_using_ext_object_cache();
		$stats['has_object_cache'] = $has_object_cache;

		// Check for CDN configuration.
		$has_cdn = false;
		
		// Check common CDN plugins.
		$cdn_plugins = array(
			'w3-total-cache/w3-total-cache.php',
			'wp-rocket/wp-rocket.php',
			'cdn-enabler/cdn-enabler.php',
		);

		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cdn = true;
				break;
			}
		}

		// Check for Cloudflare or other CDN in headers.
		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
			if ( isset( $headers['cf-ray'] ) || 
				 isset( $headers['x-cdn'] ) || 
				 isset( $headers['x-cache'] ) ) {
				$has_cdn = true;
			}
		}

		$stats['has_cdn'] = $has_cdn;

		// Check for lazy loading.
		$has_lazy_loading = false;
		
		// WordPress 5.5+ has native lazy loading.
		$wp_version = get_bloginfo( 'version' );
		if ( version_compare( $wp_version, '5.5', '>=' ) ) {
			$has_lazy_loading = true;
		}

		// Check lazy load plugins.
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

		// Check for minification.
		$has_minification = false;
		if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ||
			 is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
			 is_plugin_active( 'fast-velocity-minify/fvm.php' ) ) {
			$has_minification = true;
		}

		$stats['has_minification'] = $has_minification;

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

		// Check for database optimization schedule.
		$has_db_optimization = false;
		if ( is_plugin_active( 'wp-optimize/wp-optimize.php' ) ||
			 is_plugin_active( 'advanced-database-cleaner/advanced-db-cleaner.php' ) ) {
			$has_db_optimization = true;
		}

		$stats['has_db_optimization'] = $has_db_optimization;

		// Calculate performance infrastructure score.
		$performance_features = 0;
		$total_features = 10;

		if ( $has_monitoring ) { $performance_features++; }
		if ( $has_wpshadow_budget ) { $performance_features++; }
		if ( $has_caching ) { $performance_features++; }
		if ( $has_object_cache ) { $performance_features++; }
		if ( $has_cdn ) { $performance_features++; }
		if ( $has_lazy_loading ) { $performance_features++; }
		if ( $has_minification ) { $performance_features++; }
		if ( $has_image_optimization ) { $performance_features++; }
		if ( $has_db_optimization ) { $performance_features++; }
		if ( $has_analytics || $has_site_kit ) { $performance_features++; }

		$stats['performance_infrastructure_score'] = round( ( $performance_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( ! $has_wpshadow_budget ) {
			$warnings[] = __( 'No WPShadow performance budget configured - set performance targets in settings', 'wpshadow' );
		}

		if ( ! $has_monitoring ) {
			$warnings[] = __( 'No performance monitoring active - install Query Monitor or New Relic', 'wpshadow' );
		}

		if ( ! $has_caching ) {
			$issues[] = __( 'No caching plugin active - critical for meeting performance budgets', 'wpshadow' );
		}

		if ( ! $has_object_cache ) {
			$warnings[] = __( 'Object cache not enabled - consider Redis or Memcached for better performance', 'wpshadow' );
		}

		if ( ! $has_cdn ) {
			$warnings[] = __( 'No CDN detected - consider Cloudflare or similar for faster asset delivery', 'wpshadow' );
		}

		if ( ! $has_lazy_loading ) {
			$warnings[] = __( 'Lazy loading not enabled - helps meet performance budgets', 'wpshadow' );
		}

		if ( ! $has_minification ) {
			$warnings[] = __( 'No minification active - reduces file sizes for better performance', 'wpshadow' );
		}

		if ( ! $has_image_optimization ) {
			$warnings[] = __( 'No image optimization - images often biggest performance bottleneck', 'wpshadow' );
		}

		if ( ! $has_db_optimization ) {
			$warnings[] = __( 'No database optimization scheduled - database bloat affects performance', 'wpshadow' );
		}

		if ( ! $has_analytics && ! $has_site_kit ) {
			$warnings[] = __( 'No analytics for performance tracking - install Google Analytics or Site Kit', 'wpshadow' );
		}

		if ( $stats['performance_infrastructure_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'Performance infrastructure score is low (%s%%) - build out performance monitoring and optimization', 'wpshadow' ),
				$stats['performance_infrastructure_score']
			);
		}

		if ( empty( $active_performance_plugins ) ) {
			$warnings[] = __( 'No performance optimization plugins active - consider WP Rocket or Perfmatters', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Performance budget and monitoring has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-budget',
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
				'description'  => __( 'Performance budget has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-budget',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Performance budget and infrastructure well established.
	}
}
