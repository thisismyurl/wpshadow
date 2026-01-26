<?php
/**
 * Diagnostic: PHP OPCache Hit Rate
 *
 * Monitors PHP OPcache hit rate to ensure effective caching.
 * Low hit rate indicates cache is too small or frequently invalidated.
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
 * Class Diagnostic_Php_Opcache_Hit_Rate
 *
 * Tests PHP OPcache hit rate performance.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Opcache_Hit_Rate extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-opcache-hit-rate';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP OPCache Hit Rate';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Monitors PHP OPcache hit rate';

	/**
	 * Check PHP OPcache hit rate.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if OPcache extension is loaded.
		if ( ! extension_loaded( 'Zend OPcache' ) && ! extension_loaded( 'opcache' ) ) {
			return null; // Not applicable if OPcache not available.
		}

		// Check if OPcache is enabled.
		if ( ! function_exists( 'opcache_get_status' ) ) {
			return null; // Can't check if function not available.
		}

		$status = opcache_get_status( false );

		if ( ! $status || ! isset( $status['opcache_enabled'] ) || ! $status['opcache_enabled'] ) {
			return null; // Not applicable if OPcache not enabled.
		}

		// Get cache statistics.
		if ( ! isset( $status['opcache_statistics'] ) ) {
			return null; // Statistics not available.
		}

		$stats = $status['opcache_statistics'];

		$hits   = $stats['hits'] ?? 0;
		$misses = $stats['misses'] ?? 0;

		// Calculate hit rate.
		if ( $hits + $misses === 0 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP OPcache has no statistics yet. Wait for traffic to accumulate cache statistics.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_opcache_hit_rate',
				'meta'        => array(
					'hits'   => $hits,
					'misses' => $misses,
				),
			);
		}

		$hit_rate = ( $hits / ( $hits + $misses ) ) * 100;

		// Warn if hit rate is below 90%.
		if ( $hit_rate < 90 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Hit rate percentage */
					__( 'PHP OPcache hit rate is %s%%, which is below the recommended 90%%. Consider increasing opcache.memory_consumption or investigating frequent cache invalidations.', 'wpshadow' ),
					number_format( $hit_rate, 2 )
				),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_opcache_hit_rate',
				'meta'        => array(
					'hits'     => $hits,
					'misses'   => $misses,
					'hit_rate' => $hit_rate,
				),
			);
		}

		// PHP OPcache hit rate is healthy.
		return null;
	}
}
