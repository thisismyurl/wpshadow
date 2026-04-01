<?php
/**
 * OPcache Enabled Check Treatment
 *
 * Detects whether PHP OPcache is enabled. OPcache is PHP's built-in bytecode cache
 * that dramatically improves performance by storing precompiled script bytecode in memory.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since 0.6093.1200
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
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return array|null Finding array if OPcache disabled, null if enabled.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_OPcache_Enabled_Check' );
	}
}
