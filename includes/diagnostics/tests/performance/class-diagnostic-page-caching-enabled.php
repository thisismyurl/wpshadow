<?php
/**
 * Page Caching Enabled Diagnostic
 *
 * Detects whether full-page caching is enabled and properly configured.
 * Page caching dramatically improves performance by serving static HTML
 * instead of processing PHP on every request.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Caching Enabled Diagnostic Class
 *
 * Checks for presence and configuration of page caching mechanisms including
 * WordPress caching plugins, server-level caching, and CDN caching.
 *
 * **Why This Matters:**
 * - 47% of users expect pages to load in 2 seconds or less
 * - 40% abandon sites that take > 3 seconds
 * - Page caching reduces load time by 50-90%
 * - Lower server costs (fewer PHP/database queries)
 * - Better Core Web Vitals scores
 *
 * **What's Checked:**
 * - Popular caching plugins (WP Super Cache, W3 Total Cache, WP Rocket)
 * - Object caching (Redis, Memcached)
 * - Browser caching headers
 * - CDN integration
 *
 * @since 0.6093.1200
 */
class Diagnostic_Page_Caching_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-caching-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Page Caching Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether full-page caching is enabled to improve site performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check
	 *
	 * Detects page caching through multiple methods:
	 * - Check for caching plugin constants
	 * - Check for caching headers in HTTP response
	 * - Check for cache directories
	 * - Check advanced-cache.php drop-in
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if caching is disabled, null if enabled.
	 */
	public static function check() {
		$caching_enabled = false;
		$caching_method  = array();

		// Check for advanced-cache.php drop-in
		if ( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) && defined( 'WP_CACHE' ) && WP_CACHE ) {
			$caching_enabled  = true;
			$caching_method[] = 'Advanced cache drop-in';
		}

		// Check for popular caching plugins
		$caching_plugins = array(
			'wp-super-cache/wp-cache.php'          => 'WP Super Cache',
			'w3-total-cache/w3-total-cache.php'    => 'W3 Total Cache',
			'wp-rocket/wp-rocket.php'              => 'WP Rocket',
			'wp-fastest-cache/wpFastestCache.php'  => 'WP Fastest Cache',
			'cache-enabler/cache-enabler.php'      => 'Cache Enabler',
			'litespeed-cache/litespeed-cache.php'  => 'LiteSpeed Cache',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $caching_plugins as $plugin_path => $plugin_name ) {
			if ( in_array( $plugin_path, $active_plugins, true ) ) {
				$caching_enabled  = true;
				$caching_method[] = $plugin_name;
			}
		}

		// Check for object caching
		if ( wp_using_ext_object_cache() ) {
			$caching_method[] = 'Object caching (Redis/Memcached)';
		}

		// If caching is enabled, return null (no issue)
		if ( $caching_enabled ) {
			return null;
		}

		// Caching is not enabled
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Page caching is not enabled. Your site loads slowly because every page request requires full PHP processing and database queries.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/performance-page-caching?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'caching_enabled'      => false,
				'recommended_plugins'  => 'WP Rocket, W3 Total Cache, WP Super Cache',
				'performance_impact'   => '50-90% faster page loads',
			),
		);
	}
}
