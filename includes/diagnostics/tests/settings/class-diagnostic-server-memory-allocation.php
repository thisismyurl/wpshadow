<?php
/**
 * Server Memory Allocation Diagnostic
 *
 * Checks PHP memory limit allocation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1504
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Server Memory Allocation Diagnostic Class
 *
 * Verifies adequate memory is allocated to PHP.
 *
 * @since 1.6035.1504
 */
class Diagnostic_Server_Memory_Allocation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'server-memory-allocation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Server Memory Allocation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks PHP memory limit allocation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting-environment';

	/**
	 * Minimum memory allocation
	 *
	 * @var int
	 */
	private const MIN_MEMORY = 128 * 1024 * 1024; // 128MB

	/**
	 * Recommended memory allocation
	 *
	 * @var int
	 */
	private const RECOMMENDED_MEMORY = 256 * 1024 * 1024; // 256MB

	/**
	 * Run the memory allocation diagnostic check.
	 *
	 * @since  1.6035.1504
	 * @return array|null Finding array if memory issue detected, null otherwise.
	 */
	public static function check() {
		$memory_limit = self::parse_memory_limit( ini_get( 'memory_limit' ) );
		
		if ( $memory_limit < 0 ) {
			// Unlimited memory
			return null;
		}

		$wp_memory_limit = defined( 'WP_MEMORY_LIMIT' ) ? self::parse_memory_limit( WP_MEMORY_LIMIT ) : $memory_limit;

		if ( $memory_limit < self::MIN_MEMORY ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: current memory, 2: minimum required */
					__( 'Memory limit is %1$s, below minimum %2$s. This may cause issues with large operations.', 'wpshadow' ),
					self::format_bytes( $memory_limit ),
					self::format_bytes( self::MIN_MEMORY )
				),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/increase-php-memory',
				'meta'        => array(
					'memory_limit'     => self::format_bytes( $memory_limit ),
					'minimum_required' => self::format_bytes( self::MIN_MEMORY ),
				),
			);
		}

		if ( $memory_limit < self::RECOMMENDED_MEMORY ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: current memory, 2: recommended memory */
					__( 'Memory limit is %1$s. Recommended minimum is %2$s for optimal performance.', 'wpshadow' ),
					self::format_bytes( $memory_limit ),
					self::format_bytes( self::RECOMMENDED_MEMORY )
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/increase-php-memory',
				'meta'        => array(
					'memory_limit'  => self::format_bytes( $memory_limit ),
					'recommended'   => self::format_bytes( self::RECOMMENDED_MEMORY ),
				),
			);
		}

		return null;
	}

	/**
	 * Parse memory limit string to bytes.
	 *
	 * @since  1.6035.1504
	 * @param  string $value Memory limit value (e.g., '256M', '2G').
	 * @return int Memory in bytes, or -1 if unlimited.
	 */
	private static function parse_memory_limit( string $value ): int {
		$value = trim( $value );

		if ( '-1' === $value || 'unlimited' === strtolower( $value ) ) {
			return -1;
		}

		$value = (int) $value;
		$last_char = strtoupper( substr( $value, -1 ) );

		switch ( $last_char ) {
			case 'G':
				$value *= 1024;
				// Fall through.
			case 'M':
				$value *= 1024;
				// Fall through.
			case 'K':
				$value *= 1024;
				break;
		}

		return $value;
	}

	/**
	 * Format bytes to human readable.
	 *
	 * @since  1.6035.1504
	 * @param  int $bytes Bytes to format.
	 * @return string Formatted bytes.
	 */
	private static function format_bytes( int $bytes ): string {
		$units = array( 'B', 'KB', 'MB', 'GB' );
		$bytes = max( $bytes, 0 );
		$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow = min( $pow, count( $units ) - 1 );
		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}
}
