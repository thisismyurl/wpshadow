<?php
/**
 * Performance Baseline Diagnostic
 *
 * Checks if performance metrics are being tracked and baselines established.
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
 * Performance Baseline Diagnostic Class
 *
 * Verifies that performance monitoring is in place with established
 * baselines for tracking improvements and regressions.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Performance_Baseline extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'performance-baseline';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Baseline Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if performance metrics are being tracked and baselines established';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the performance baseline diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if baseline issues detected, null otherwise.
	 */
	public static function check() {
		$issues   = array();
		$warnings = array();
		$metrics  = array();

		// Check for performance monitoring plugins.
		$performance_plugins = array(
			'query-monitor/query-monitor.php',
			'p3-profiler/p3-profiler.php',
			'wp-performance-profiler/profiler.php',
		);

		$has_monitoring  = false;
		$active_monitors = array();

		foreach ( $performance_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_monitoring    = true;
				$active_monitors[] = dirname( $plugin );
			}
		}

		if ( ! $has_monitoring ) {
			$issues[] = __( 'No performance monitoring plugin detected', 'wpshadow' );
		}

		// Check for object cache.
		if ( ! wp_using_ext_object_cache() ) {
			$warnings[] = __( 'Object cache not enabled - consider Redis or Memcached', 'wpshadow' );
		} else {
			$metrics['object_cache'] = 'enabled';
		}

		// Check for page cache.
		$cache_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'cache-enabler/cache-enabler.php',
			'litespeed-cache/litespeed-cache.php',
		);

		$has_page_cache = false;
		foreach ( $cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_page_cache        = true;
				$metrics['page_cache'] = dirname( $plugin );
				break;
			}
		}

		if ( ! $has_page_cache ) {
			$warnings[] = __( 'No page caching plugin detected', 'wpshadow' );
		}

		// Check server response time baseline.
		$start_time = microtime( true );
		global $wpdb;
		$wpdb->get_var( 'SELECT 1' );
		$db_query_time = microtime( true ) - $start_time;

		$metrics['db_query_time_ms'] = round( $db_query_time * 1000, 2 );

		if ( $db_query_time > 0.1 ) {
			$warnings[] = sprintf(
				/* translators: %s: query time in milliseconds */
				__( 'Database query time high: %sms', 'wpshadow' ),
				$metrics['db_query_time_ms']
			);
		}

		// Check PHP version (newer = faster).
		$php_version            = phpversion();
		$metrics['php_version'] = $php_version;

		if ( version_compare( $php_version, '7.4', '<' ) ) {
			$issues[] = sprintf(
				/* translators: %s: PHP version */
				__( 'PHP version outdated (%s) - upgrade for better performance', 'wpshadow' ),
				$php_version
			);
		} elseif ( version_compare( $php_version, '8.0', '<' ) ) {
			$warnings[] = sprintf(
				/* translators: %s: PHP version */
				__( 'PHP version (%s) could be upgraded to 8.0+ for performance gains', 'wpshadow' ),
				$php_version
			);
		}

		// Check for CDN.
		$cdn_plugins = array(
			'cloudflare/cloudflare.php',
			'jetpack/jetpack.php',
		);

		$has_cdn = false;
		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cdn        = true;
				$metrics['cdn'] = dirname( $plugin );
				break;
			}
		}

		if ( ! $has_cdn ) {
			$warnings[] = __( 'No CDN detected - consider using for static assets', 'wpshadow' );
		}

		// Check for lazy loading.
		$has_lazy_load = false;

		// WordPress 5.5+ has native lazy loading.
		if ( function_exists( 'wp_lazy_loading_enabled' ) ) {
			$has_lazy_load        = true;
			$metrics['lazy_load'] = 'native';
		} else {
			$lazy_load_plugins = array(
				'lazy-load/lazy-load.php',
				'a3-lazy-load/a3-lazy-load.php',
			);

			foreach ( $lazy_load_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$has_lazy_load        = true;
					$metrics['lazy_load'] = dirname( $plugin );
					break;
				}
			}
		}

		if ( ! $has_lazy_load ) {
			$warnings[] = __( 'Lazy loading not detected for images', 'wpshadow' );
		}

		// Check for minification.
		$has_minification = false;
		foreach ( $cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_minification = true; // Most cache plugins include minification.
				break;
			}
		}

		if ( ! $has_minification ) {
			$warnings[] = __( 'No CSS/JS minification detected', 'wpshadow' );
		}

		// Check database optimization.
		$table_count                = $wpdb->get_var( 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()' );
		$metrics['database_tables'] = (int) $table_count;

		if ( $table_count > 100 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of tables */
				__( 'Large number of database tables (%d) - consider cleanup', 'wpshadow' ),
				$table_count
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Performance baseline has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-baseline?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'has_monitoring'  => $has_monitoring,
					'active_monitors' => $active_monitors,
					'metrics'         => $metrics,
					'issues'          => $issues,
					'warnings'        => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Performance baseline has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-baseline?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'has_monitoring'  => $has_monitoring,
					'active_monitors' => $active_monitors,
					'metrics'         => $metrics,
					'warnings'        => $warnings,
				),
			);
		}

		return null; // Performance baseline is well established.
	}
}
