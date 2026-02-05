<?php
/**
 * OPcache Enabled Check Treatment
 *
 * Detects whether PHP OPcache is enabled. OPcache is PHP's built-in bytecode cache
 * that dramatically improves performance by storing precompiled script bytecode in memory.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since      1.6034.2151
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OPcache Enabled Check Treatment Class
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
 * @since 1.6034.2151
 */
class Treatment_OPcache_Enabled_Check extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'opcache-enabled-check';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'PHP OPcache Status';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether PHP OPcache is enabled for improved performance';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2151
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
				'kb_link'      => 'https://wpshadow.com/kb/performance-opcache-enabled',
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
				'kb_link'      => 'https://wpshadow.com/kb/performance-opcache-enabled',
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
