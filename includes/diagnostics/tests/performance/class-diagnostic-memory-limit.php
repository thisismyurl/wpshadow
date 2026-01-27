<?php
/**
 * Diagnostic: PHP Memory Limit Configuration
 *
 * Checks if PHP memory limit is set to recommended levels.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Memory_Limit Class
 *
 * Detects if PHP memory limit is insufficient for WordPress operations.
 * Low memory limits can cause fatal errors during:
 * - Plugin and theme updates
 * - Bulk imports and exports
 * - Media processing and optimization
 * - WooCommerce operations
 * - Database backups
 *
 * WordPress recommends at least 256MB, but 512MB+ is optimal for most sites.
 * Sites with many plugins, custom post types, or heavy media processing
 * may need even more.
 *
 * Returns different threat levels based on memory limit configuration.
 *
 * @since 1.2601.2200
 */
class Diagnostic_Memory_Limit extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'memory-limit';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'PHP Memory Limit';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP memory allocation is sufficient for WordPress operations';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks the current PHP memory limit and compares against thresholds:
	 * - Below 256M: Medium priority (common in budget hosting)
	 * - 256M-512M: Recommended (acceptable but tight)
	 * - 512M+: Good (optimal for most sites)
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if memory limit is too low, null if adequate.
	 */
	public static function check() {
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = self::convert_to_bytes( $memory_limit );

		// WordPress recommended minimum
		$min_recommended = 256 * 1024 * 1024; // 256M
		$optimal         = 512 * 1024 * 1024; // 512M

		// Critical: Below WordPress minimum (40MB is absolute minimum)
		if ( $memory_bytes < 40 * 1024 * 1024 ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current memory, 2: minimum recommended */
					esc_html__( 'Your PHP memory limit is set to %1$s, which is too low. WordPress requires at least %2$s to function properly. You will experience fatal errors during updates and bulk operations.', 'wpshadow' ),
					$memory_limit,
					'256MB'
				),
				'severity'           => 'high',
				'threat_level'       => 75,
				'site_health_status' => 'critical',
				'auto_fixable'       => true,
				'kb_link'            => 'https://wpshadow.com/kb/performance-memory-limit',
				'family'             => self::$family,
				'details'            => array(
					'current_memory'  => $memory_limit,
					'current_bytes'   => $memory_bytes,
					'minimum_bytes'   => $min_recommended,
					'optimal_bytes'   => $optimal,
					'recommendation'  => 'Increase to at least 256MB (512MB+ recommended)',
				),
			);
		}

		// Medium: Below recommended (256M)
		if ( $memory_bytes < $min_recommended ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current memory, 2: recommended memory */
					esc_html__( 'Your PHP memory limit is %1$s. WordPress recommends at least %2$s for reliable operation. Consider increasing this to prevent timeouts during updates and bulk imports.', 'wpshadow' ),
					$memory_limit,
					'256MB'
				),
				'severity'           => 'medium',
				'threat_level'       => 60,
				'site_health_status' => 'recommended',
				'auto_fixable'       => true,
				'kb_link'            => 'https://wpshadow.com/kb/performance-memory-limit',
				'family'             => self::$family,
				'details'            => array(
					'current_memory' => $memory_limit,
					'current_bytes'  => $memory_bytes,
					'minimum_bytes'  => $min_recommended,
					'optimal_bytes'  => $optimal,
					'recommendation' => 'Increase to 256MB or higher',
				),
			);
		}

		// Low: Between 256M and 512M (acceptable but not optimal)
		if ( $memory_bytes < $optimal ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current memory, 2: optimal memory */
					esc_html__( 'Your PHP memory limit is %1$s, which meets WordPress requirements. However, %2$s or higher is optimal for sites with many plugins or heavy media processing.', 'wpshadow' ),
					$memory_limit,
					'512MB'
				),
				'severity'           => 'low',
				'threat_level'       => 35,
				'site_health_status' => 'recommended',
				'auto_fixable'       => true,
				'kb_link'            => 'https://wpshadow.com/kb/performance-memory-limit',
				'family'             => self::$family,
				'details'            => array(
					'current_memory' => $memory_limit,
					'current_bytes'  => $memory_bytes,
					'minimum_bytes'  => $min_recommended,
					'optimal_bytes'  => $optimal,
					'recommendation' => 'Consider increasing to 512MB+ for better performance',
				),
			);
		}

		// All good - memory limit is optimal
		return null;
	}

	/**
	 * Convert memory limit notation to bytes.
	 *
	 * Converts PHP memory notations like "256M" or "512M" to bytes.
	 * Handles: K (kilobytes), M (megabytes), G (gigabytes)
	 *
	 * @since  1.2601.2200
	 * @param  string $value Memory limit string (e.g., "256M", "512MB").
	 * @return int Memory in bytes.
	 */
	private static function convert_to_bytes( string $value ): int {
		$value = trim( $value );

		// If it's already numeric (already in bytes or -1 for unlimited)
		if ( is_numeric( $value ) ) {
			return (int) $value;
		}

		// Get the last character which indicates the unit
		$unit  = strtoupper( substr( $value, -1 ) );
		$bytes = (int) $value;

		switch ( $unit ) {
			case 'K':
				return $bytes * 1024;
			case 'M':
				return $bytes * 1024 * 1024;
			case 'G':
				return $bytes * 1024 * 1024 * 1024;
			default:
				return $bytes;
		}
	}
}
