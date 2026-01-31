<?php
/**
 * Diagnostic: PHP OPCache Status
 *
 * Checks if PHP OPcache is enabled and properly configured.
 * OPcache significantly improves PHP performance by caching compiled scripts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Opcache_Status
 *
 * Tests PHP OPcache configuration and status.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Opcache_Status extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-opcache-status';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP OPCache Status';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP OPcache is enabled';

	/**
	 * Check PHP OPcache status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if OPcache extension is loaded.
		if ( ! extension_loaded( 'Zend OPcache' ) && ! extension_loaded( 'opcache' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP OPcache is not installed. Enabling OPcache can improve PHP performance by 30-70% by caching compiled scripts.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_opcache_status',
				'meta'        => array(
					'opcache_loaded'  => false,
					'opcache_enabled' => false,
				),
			);
		}

		// Check if OPcache is enabled.
		if ( ! function_exists( 'opcache_get_status' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP OPcache extension is loaded but opcache_get_status() function is not available. OPcache may not be properly configured.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_opcache_status',
				'meta'        => array(
					'opcache_loaded'  => true,
					'opcache_enabled' => false,
				),
			);
		}

		// Get OPcache status.
		$status = opcache_get_status( false );

		if ( ! $status || ! isset( $status['opcache_enabled'] ) || ! $status['opcache_enabled'] ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP OPcache is installed but not enabled. Enable it in php.ini (opcache.enable=1) to improve performance.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_opcache_status',
				'meta'        => array(
					'opcache_loaded'  => true,
					'opcache_enabled' => false,
				),
			);
		}

		// Check OPcache memory usage.
		if ( isset( $status['memory_usage'] ) ) {
			$memory_used  = $status['memory_usage']['used_memory'] ?? 0;
			$memory_free  = $status['memory_usage']['free_memory'] ?? 0;
			$memory_total = $memory_used + $memory_free;

			// Warn if using > 90% of OPcache memory.
			if ( $memory_total > 0 && ( $memory_used / $memory_total ) > 0.9 ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => sprintf(
						/* translators: 1: Used memory percentage */
						__( 'PHP OPcache is using %1$s%% of allocated memory. Consider increasing opcache.memory_consumption in php.ini.', 'wpshadow' ),
						number_format( ( $memory_used / $memory_total ) * 100, 1 )
					),
					'severity'    => 'info',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/php_opcache_status',
					'meta'        => array(
						'opcache_loaded'  => true,
						'opcache_enabled' => true,
						'memory_used'     => $memory_used,
						'memory_total'    => $memory_total,
						'memory_percent'  => ( $memory_used / $memory_total ) * 100,
					),
				);
			}
		}

		// PHP OPcache is enabled and functioning properly.
		return null;
	}
}
