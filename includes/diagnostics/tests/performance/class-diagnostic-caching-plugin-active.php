<?php
/**
 * Caching Plugin Active Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 66.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caching Plugin Active Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Caching_Plugin_Active extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'caching-plugin-active';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Caching Plugin Active';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Caching Plugin Active. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check active plugin list for recognized caching tools.
	 *
	 * TODO Fix Plan:
	 * Fix by installing/enabling caching plugin.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		// Check for the WP_CACHE constant (set by page caching plugins in wp-config.php).
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			return null;
		}

		// Check for active page-caching plugin files.
		$cache_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'wp-rocket/wp-rocket.php',
			'cache-enabler/cache-enabler.php',
			'litespeed-cache/litespeed-cache.php',
			'comet-cache/comet-cache.php',
			'wp-optimize/wp-optimize.php',
			'breeze/breeze.php',
			'hummingbird-performance/wp-hummingbird.php',
			'sg-cachepress/sg-cachepress.php',
			'swift-performance-lite/performance.php',
			'autoptimize/autoptimize.php',
			'nitropack/nitropack.php',
			'siteground-optimizer/index.php',
		);

		$active_plugins = (array) get_option( 'active_plugins', array() );

		foreach ( $cache_plugins as $plugin_file ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				return null;
			}
		}

		// Check for host-provided caching classes (e.g. Kinsta, Flywheel, WPEngine, SiteGround).
		$cache_classes = array(
			'WpeCommon',         // WP Engine
			'Kinsta\\Cache',      // Kinsta
			'KinstaCache',
			'Breeze',
			'LiteSpeed_Cache',
		);

		foreach ( $cache_classes as $class ) {
			if ( class_exists( $class, false ) ) {
				return null;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No page caching solution was detected. Page caching is the single most impactful WordPress performance optimisation, typically reducing server load by 50–90% and cutting response times from hundreds of milliseconds to tens. Install a caching plugin such as WP Super Cache, LiteSpeed Cache, or WP Rocket.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/caching-plugin-active',
			'details'      => array(
				'wp_cache_constant' => defined( 'WP_CACHE' ) ? WP_CACHE : false,
				'note'              => __( 'Recommended: WP Super Cache (free), LiteSpeed Cache (free), or WP Rocket (paid).', 'wpshadow' ),
			),
		);
	}
}
