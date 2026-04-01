<?php
/**
 * OPcache Configuration Optimization Diagnostic
 *
 * Analyzes OPcache configuration settings and recommends optimizations
 * for WordPress-specific workloads.
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
 * OPcache Configuration Optimization Diagnostic Class
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
 * @since 0.6093.1200
 */
class Diagnostic_OPcache_Configuration_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'opcache-configuration-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'OPcache Configuration Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes OPcache configuration and recommends optimizations for WordPress';

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
	 * @return array|null Finding array if suboptimal, null if well-configured.
	 */
	public static function check() {
		if ( ! extension_loaded( 'Zend OPcache' ) || ! ini_get( 'opcache.enable' ) ) {
			return null; // Separate diagnostic handles this
		}

		$issues        = array();
		$current_config = array();

		// Check memory_consumption
		$memory_consumption = (int) ini_get( 'opcache.memory_consumption' );
		$current_config['memory_consumption'] = $memory_consumption;
		if ( $memory_consumption < 128 ) {
			$issues[] = sprintf(
				/* translators: %d: current memory in MB */
				__( 'opcache.memory_consumption is %dMB (recommended: 128MB+)', 'wpshadow' ),
				$memory_consumption
			);
		}

		// Check max_accelerated_files
		$max_files = (int) ini_get( 'opcache.max_accelerated_files' );
		$current_config['max_accelerated_files'] = $max_files;
		if ( $max_files < 10000 ) {
			$issues[] = sprintf(
				/* translators: %d: current file limit */
				__( 'opcache.max_accelerated_files is %d (recommended: 10000+)', 'wpshadow' ),
				$max_files
			);
		}

		// Check interned_strings_buffer
		$strings_buffer = (int) ini_get( 'opcache.interned_strings_buffer' );
		$current_config['interned_strings_buffer'] = $strings_buffer;
		if ( $strings_buffer < 16 ) {
			$issues[] = sprintf(
				/* translators: %d: current buffer in MB */
				__( 'opcache.interned_strings_buffer is %dMB (recommended: 16MB+)', 'wpshadow' ),
				$strings_buffer
			);
		}

		// Check validate_timestamps (should be 0 in production)
		$validate_timestamps = (int) ini_get( 'opcache.validate_timestamps' );
		$current_config['validate_timestamps'] = $validate_timestamps;
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			if ( $validate_timestamps === 1 ) {
				$issues[] = __( 'opcache.validate_timestamps is enabled (recommended: disabled for production)', 'wpshadow' );
			}
		}

		if ( empty( $issues ) ) {
			return null; // Configuration is optimal
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of configuration issues */
				__( '%d OPcache configuration issue(s) detected. Optimizing these settings will improve performance.', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/performance-opcache-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues'          => $issues,
				'current_config'  => $current_config,
				'recommended'     => array(
					'memory_consumption'         => '128-256',
					'max_accelerated_files'      => '10000',
					'interned_strings_buffer'    => '16',
					'validate_timestamps'        => '0 (production)',
				),
			),
		);
	}
}
