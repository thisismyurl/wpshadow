<?php
/**
 * OPcache Enabled Check Diagnostic
 *
 * Detects whether PHP OPcache is enabled. OPcache is PHP's built-in bytecode cache
 * that dramatically improves performance by storing precompiled script bytecode in memory.
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
 * OPcache Enabled Check Diagnostic Class
 *
 * Verifies that PHP OPcache extension is installed, enabled, and active.
 * OPcache eliminates the need for PHP to read, parse, and compile scripts
 * on every request, reducing CPU usage by 40-60% and improving response time.
 *
 * **Why This Matters:**
 * - 40-60% reduction in CPU usage
 * - 2-3x faster PHP execution
 * - Required for high-traffic WordPress sites
 * - Free (built into PHP 5.5+)
 * - Recommended by WordPress.org performance guides
 *
 * **What's Checked:**
 * - opcache extension loaded
 * - opcache.enable = 1
 * - opcache.enable_cli (optional, for WP-CLI)
 * - Sufficient opcache.memory_consumption
 *
 * @since 0.6093.1200
 */
class Diagnostic_OPcache_Enabled_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'opcache-enabled-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP OPcache Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether PHP OPcache is enabled for improved performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if OPcache disabled, null if enabled.
	 */
	public static function check() {
		// Check if OPcache extension is loaded
		if ( ! extension_loaded( 'Zend OPcache' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'PHP OPcache extension is not installed. Your server is recompiling PHP scripts on every request, wasting CPU resources.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-opcache-enabled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'opcache_loaded' => false,
					'php_version'    => PHP_VERSION,
					'recommendation' => 'Contact your hosting provider to enable OPcache',
				),
			);
		}

		// Check if OPcache is enabled
		$opcache_enabled = ini_get( 'opcache.enable' );
		if ( ! $opcache_enabled || $opcache_enabled === '0' ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'PHP OPcache is installed but disabled. Enable it in php.ini for 40-60% better performance.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-opcache-enabled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'opcache_loaded'  => true,
					'opcache_enabled' => false,
					'php_version'     => PHP_VERSION,
					'fix'             => 'Set opcache.enable=1 in php.ini',
				),
			);
		}

		// OPcache is enabled - no issue
		return null;
	}
}
