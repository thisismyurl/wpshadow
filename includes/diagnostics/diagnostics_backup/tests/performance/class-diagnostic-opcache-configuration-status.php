<?php
/**
 * Opcache Configuration Status Diagnostic
 *
 * Verifies PHP opcache is enabled and properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Opcache Configuration Status Class
 *
 * Tests whether PHP opcache is enabled and optimized.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Opcache_Configuration_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'opcache-configuration-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Opcache Configuration Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies PHP opcache is enabled and properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if opcache is available.
		if ( ! function_exists( 'opcache_get_status' ) ) {
			$issues[] = __( 'PHP opcache extension not available (3-5x performance loss)', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/opcache-configuration-status',
				'meta'         => array(
					'opcache_available' => false,
				),
			);
		}

		// Get opcache status.
		$status = opcache_get_status( false );
		
		if ( ! $status || empty( $status['opcache_enabled'] ) ) {
			$issues[] = __( 'PHP opcache installed but disabled (enable with opcache.enable=1)', 'wpshadow' );
		} else {
			// Opcache is enabled, check configuration.
			$config_issues = self::check_opcache_configuration( $status );
			if ( ! empty( $config_issues ) ) {
				$issues = array_merge( $issues, $config_issues );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/opcache-configuration-status',
				'meta'         => array(
					'opcache_enabled' => ! empty( $status['opcache_enabled'] ),
					'memory_usage'    => isset( $status['memory_usage'] ) ? $status['memory_usage'] : array(),
					'opcache_stats'   => isset( $status['opcache_statistics'] ) ? $status['opcache_statistics'] : array(),
					'issues_found'    => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Check opcache configuration for issues.
	 *
	 * @since  1.26028.1905
	 * @param  array $status Opcache status array.
	 * @return array Array of configuration issues.
	 */
	private static function check_opcache_configuration( $status ) {
		$issues = array();

		// Check memory usage.
		if ( isset( $status['memory_usage'] ) ) {
			$memory = $status['memory_usage'];
			$memory_used = isset( $memory['used_memory'] ) ? $memory['used_memory'] : 0;
			$memory_free = isset( $memory['free_memory'] ) ? $memory['free_memory'] : 0;
			$memory_total = $memory_used + $memory_free;

			if ( $memory_total > 0 ) {
				$memory_percent = ( $memory_used / $memory_total ) * 100;
				
				if ( $memory_percent > 90 ) {
					$issues[] = sprintf(
						/* translators: %d: memory usage percentage */
						__( 'Opcache memory %d%% full (increase opcache.memory_consumption)', 'wpshadow' ),
						round( $memory_percent )
					);
				}

				// Check if memory is too small for WordPress.
				if ( $memory_total < 128 * 1024 * 1024 ) { // Less than 128MB.
					$issues[] = sprintf(
						/* translators: %s: current memory size */
						__( 'Opcache memory too small (%s) - recommend 128MB minimum for WordPress', 'wpshadow' ),
						size_format( $memory_total )
					);
				}
			}
		}

		// Check hit rate.
		if ( isset( $status['opcache_statistics'] ) ) {
			$stats = $status['opcache_statistics'];
			$hits = isset( $stats['hits'] ) ? $stats['hits'] : 0;
			$misses = isset( $stats['misses'] ) ? $stats['misses'] : 0;
			$total = $hits + $misses;

			if ( $total > 100 ) { // Need some requests for valid sample.
				$hit_rate = ( $hits / $total ) * 100;
				
				if ( $hit_rate < 95 ) {
					$issues[] = sprintf(
						/* translators: %d: hit rate percentage */
						__( 'Opcache hit rate only %d%% (should be >95%% - may need more memory)', 'wpshadow' ),
						round( $hit_rate )
					);
				}
			}
		}

		// Check configuration settings.
		$config = opcache_get_configuration();
		if ( isset( $config['directives'] ) ) {
			$directives = $config['directives'];

			// Check if interned strings optimization is enabled.
			if ( isset( $directives['opcache.save_comments'] ) && ! $directives['opcache.save_comments'] ) {
				$issues[] = __( 'opcache.save_comments disabled (can break WordPress/plugins)', 'wpshadow' );
			}

			// Check revalidate frequency.
			if ( isset( $directives['opcache.revalidate_freq'] ) ) {
				$revalidate_freq = (int) $directives['opcache.revalidate_freq'];
				if ( $revalidate_freq < 60 ) {
					$issues[] = sprintf(
						/* translators: %d: revalidate frequency in seconds */
						__( 'opcache.revalidate_freq too frequent (%ds) - recommend 60+ seconds for production', 'wpshadow' ),
						$revalidate_freq
					);
				}
			}

			// Check if opcache.validate_timestamps is disabled (production optimization).
			if ( isset( $directives['opcache.validate_timestamps'] ) && $directives['opcache.validate_timestamps'] ) {
				// This is actually fine for development, but note it for production.
				// Not adding as issue since it's common and not critical.
			}
		}

		return $issues;
	}
}
