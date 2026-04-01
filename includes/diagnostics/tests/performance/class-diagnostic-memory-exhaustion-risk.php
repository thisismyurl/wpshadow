<?php
/**
 * Memory Exhaustion Risk Diagnostic
 *
 * Checks current memory usage versus PHP memory limit.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Memory_Exhaustion_Risk Class
 *
 * Flags when memory usage approaches configured limits.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Memory_Exhaustion_Risk extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'memory-exhaustion-risk';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Memory Exhaustion Risk';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether memory usage is near the PHP limit';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$limit = ini_get( 'memory_limit' );
		$limit_bytes = self::parse_size( $limit );
		$usage_bytes = memory_get_usage( true );

		if ( $limit_bytes <= 0 ) {
			return null;
		}

		$usage_pct = ( $usage_bytes / $limit_bytes ) * 100;

		if ( $usage_pct >= 90 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Memory usage is above 90% of the PHP limit. Risk of fatal errors.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/memory-exhaustion-risk?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'usage_pct' => round( $usage_pct, 2 ),
				),
			);
		}

		if ( $usage_pct >= 75 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Memory usage is above 75% of the PHP limit. Consider optimization or higher limits.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/memory-exhaustion-risk?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'usage_pct' => round( $usage_pct, 2 ),
				),
			);
		}

		return null;
	}

	/**
	 * Parse size string like 128M into bytes.
	 *
	 * @since 0.6093.1200
	 * @param  string|false $value Memory limit string.
	 * @return int Size in bytes.
	 */
	private static function parse_size( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return 0;
		}

		$value = trim( $value );
		if ( '-1' === $value ) {
			return PHP_INT_MAX;
		}

		$unit  = strtoupper( substr( $value, -1 ) );
		$bytes = (int) $value;

		switch ( $unit ) {
			case 'G':
				$bytes *= 1024;
				// no break
			case 'M':
				$bytes *= 1024;
				// no break
			case 'K':
				$bytes *= 1024;
		}

		return $bytes;
	}
}