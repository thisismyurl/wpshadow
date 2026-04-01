<?php
/**
 * Page Load Speed Diagnostic
 *
 * Checks if homepage meets Core Web Vitals performance standards (<2 seconds).
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
 * Page Load Speed Diagnostic Class
 *
 * Verifies that the homepage loads in under 2 seconds and meets
 * Core Web Vitals performance standards.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Page_Load_Speed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-load-speed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Page Load Speed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if homepage meets Core Web Vitals performance standards (<2 seconds)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the page load speed diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if performance issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for performance monitoring plugin.
		$performance_plugins = array(
			'query-monitor/query-monitor.php',
			'debug-bar/debug-bar.php',
		);

		$has_performance_monitoring = false;
		foreach ( $performance_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_performance_monitoring = true;
				break;
			}
		}

		// Simulate homepage request timing.
		$start_time = microtime( true );

		// Get homepage.
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array(
			'timeout'   => 10,
			'blocking'  => true,
			'sslverify' => false,
		) );

		$end_time = microtime( true );
		$load_time = ( $end_time - $start_time );

		$stats['homepage_load_time'] = round( $load_time, 2 );
		$stats['load_time_seconds'] = round( $load_time, 2 );

		// Check for successful response.
		if ( is_wp_error( $response ) ) {
			$issues[] = sprintf(
				/* translators: %s: error message */
				__( 'Failed to measure page speed: %s', 'wpshadow' ),
				$response->get_error_message()
			);
			return null;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			$issues[] = sprintf(
				/* translators: %d: HTTP code */
				__( 'Homepage returned HTTP %d instead of 200', 'wpshadow' ),
				$response_code
			);
		}

		// Check load time thresholds.
		// Excellent: < 1s, Good: 1-2s, Poor: > 2s.
		if ( $load_time > 3 ) {
			$issues[] = sprintf(
				/* translators: %s: time in seconds */
				__( 'Homepage load time critically slow: %s seconds', 'wpshadow' ),
				round( $load_time, 2 )
			);
		} elseif ( $load_time > 2 ) {
			$warnings[] = sprintf(
				/* translators: %s: time in seconds */
				__( 'Homepage load time exceeds Core Web Vitals target: %s seconds (target: <2s)', 'wpshadow' ),
				round( $load_time, 2 )
			);
		} elseif ( $load_time > 1 ) {
			$warnings[] = sprintf(
				/* translators: %s: time in seconds */
				__( 'Homepage load time is good but could be optimized: %s seconds (excellent: <1s)', 'wpshadow' ),
				round( $load_time, 2 )
			);
		}

		// Check for caching.
		$cache_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
		);

		$has_caching = false;
		foreach ( $cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_caching = true;
				break;
			}
		}

		$stats['caching_enabled'] = $has_caching;

		if ( ! $has_caching ) {
			$warnings[] = __( 'No caching plugin detected - consider enabling page cache', 'wpshadow' );
		}

		// Check for compression.
		if ( function_exists( 'gzcompress' ) ) {
			$stats['compression_available'] = true;
		}

		// Check number of posts on homepage.
		$query = new \WP_Query( array(
			'posts_per_page' => -1,
			'post_type'      => 'post',
			'post_status'    => 'publish',
		) );

		$post_count = $query->found_posts;
		$stats['published_posts'] = $post_count;

		if ( $post_count > 100 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of posts */
				__( 'Large number of published posts (%d) may impact performance', 'wpshadow' ),
				$post_count
			);
		}

		// Check for unused plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$stats['active_plugins'] = count( $active_plugins );

		if ( count( $active_plugins ) > 30 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of plugins */
				__( 'High number of active plugins (%d) may impact page speed', 'wpshadow' ),
				count( $active_plugins )
			);
		}

		// Check for heavy widgets.
		global $wp_registered_sidebars;
		if ( ! empty( $wp_registered_sidebars ) ) {
			$widget_count = 0;
			foreach ( $wp_registered_sidebars as $sidebar ) {
				$sidebar_widgets = wp_get_sidebars_widgets()[ $sidebar['id'] ] ?? array();
				$widget_count += count( $sidebar_widgets );
			}

			$stats['active_widgets'] = $widget_count;

			if ( $widget_count > 15 ) {
				$warnings[] = sprintf(
					/* translators: %d: number of widgets */
					__( 'Large number of active widgets (%d) may impact performance', 'wpshadow' ),
					$widget_count
				);
			}
		}

		// Check WordPress version (older versions are slower).
		global $wp_version;
		$stats['wordpress_version'] = $wp_version;

		if ( version_compare( $wp_version, '6.0', '<' ) ) {
			$warnings[] = sprintf(
				/* translators: %s: WordPress version */
				__( 'WordPress version %s is outdated - upgrade for better performance', 'wpshadow' ),
				$wp_version
			);
		}

		// Check PHP version.
		$php_version = phpversion();
		$stats['php_version'] = $php_version;

		if ( version_compare( $php_version, '8.0', '<' ) ) {
			$warnings[] = sprintf(
				/* translators: %s: PHP version */
				__( 'PHP version %s is outdated - upgrade to 8.1+ for better performance', 'wpshadow' ),
				$php_version
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Page speed has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/page-load-speed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
				'description'  => __( 'Page speed has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/page-load-speed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Page speed is good.
	}
}
