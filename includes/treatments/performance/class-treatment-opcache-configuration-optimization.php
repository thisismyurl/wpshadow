<?php
/**
 * OPcache Configuration Optimization Treatment
 *
 * Analyzes OPcache configuration settings and recommends optimizations
 * for WordPress-specific workloads.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OPcache Configuration Optimization Treatment Class
 *
 * Checks OPcache configuration against WordPress best practices.
 * Even when enabled, poorly configured OPcache can cause cache thrashing,
 * out-of-memory errors, or suboptimal performance.
 *
 * **Why This Matters:**
 * - Default OPcache settings too conservative for WordPress
 * - Cache thrashing wastes CPU recompiling scripts
 * - Insufficient memory causes OOM errors
 * - Improper settings = lost performance gains
 *
 * **Optimal Settings for WordPress:**
 * - opcache.memory_consumption: 128-256MB
 * - opcache.max_accelerated_files: 10000+
 * - opcache.validate_timestamps: 0 (production)
 * - opcache.revalidate_freq: 0 (if timestamps disabled)
 *
 * @since 1.6093.1200
 */
class Treatment_OPcache_Configuration_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'opcache-configuration-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'OPcache Configuration Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes OPcache configuration and recommends optimizations for WordPress';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if suboptimal, null if well-configured.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_OPcache_Configuration_Optimization' );
	}
}
