<?php
/**
 * Diagnostic: Imagick Resource Limits
 *
 * Checks Imagick resource limits (memory, disk, threads) for optimization.
 * Proper limits prevent server overload and improve image processing performance.
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
 * Class Diagnostic_Imagick_Resource_Limits
 *
 * Monitors Imagick resource limit configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Imagick_Resource_Limits extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'imagick-resource-limits';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Imagick Resource Limits';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks Imagick resource limits for optimization';

	/**
	 * Check Imagick resource limits.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if Imagick is available.
		if ( ! extension_loaded( 'imagick' ) ) {
			return null; // Not applicable if Imagick not installed.
		}

		// Get Imagick resource limits.
		$imagick = new \Imagick();
		$limits  = array();

		// Check memory limit.
		$memory_limit = $imagick->getResourceLimit( \Imagick::RESOURCETYPE_MEMORY );
		$limits['memory'] = $memory_limit;

		// Check disk limit.
		$disk_limit = $imagick->getResourceLimit( \Imagick::RESOURCETYPE_DISK );
		$limits['disk'] = $disk_limit;

		// Check thread limit.
		$thread_limit = $imagick->getResourceLimit( \Imagick::RESOURCETYPE_THREAD );
		$limits['thread'] = $thread_limit;

		// Check area limit (pixels).
		$area_limit = $imagick->getResourceLimit( \Imagick::RESOURCETYPE_AREA );
		$limits['area'] = $area_limit;

		$issues = array();

		// Check if memory limit is too low (less than 256MB).
		$recommended_memory = 256 * 1024 * 1024; // 256MB in bytes.
		if ( $memory_limit > 0 && $memory_limit < $recommended_memory ) {
			$issues[] = sprintf(
				/* translators: %s: Current memory limit in human-readable format */
				__( 'Memory limit is low: %s (recommended: 256MB+)', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Check if thread limit is set (0 = unlimited, which is good).
		if ( $thread_limit > 0 && $thread_limit < 2 ) {
			$issues[] = sprintf(
				/* translators: %d: Current thread limit */
				__( 'Thread limit is too restrictive: %d (recommended: 2+)', 'wpshadow' ),
				$thread_limit
			);
		}

		// Check if area limit is too restrictive.
		$recommended_area = 25000000; // ~5000x5000 pixels.
		if ( $area_limit > 0 && $area_limit < $recommended_area ) {
			$issues[] = sprintf(
				/* translators: %s: Current area limit */
				__( 'Area limit may be too restrictive: %s pixels (recommended: 25MP+)', 'wpshadow' ),
				number_format_i18n( $area_limit )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: List of issues */
					__( 'Imagick resource limits may need adjustment: %s', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/imagick_resource_limits',
				'meta'        => array(
					'limits' => $limits,
					'issues' => $issues,
				),
			);
		}

		// Imagick resource limits are appropriate.
		return null;
	}
}
