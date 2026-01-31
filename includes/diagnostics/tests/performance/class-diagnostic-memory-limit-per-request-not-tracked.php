<?php
/**
 * Memory Limit Per Request Not Tracked Diagnostic
 *
 * Checks if memory usage per request is monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Memory Limit Per Request Not Tracked Diagnostic Class
 *
 * Detects missing memory monitoring.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Memory_Limit_Per_Request_Not_Tracked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'memory-limit-per-request-not-tracked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Memory Limit Per Request Not Tracked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if memory usage is tracked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$memory_limit = WP_MEMORY_LIMIT;

		// Convert to bytes for comparison
		if ( function_exists( 'wp_convert_bytes_to_hr' ) ) {
			// Check if memory limit is reasonable
			if ( strpos( $memory_limit, 'M' ) !== false ) {
				$memory_mb = (int) $memory_limit;
				if ( $memory_mb < 64 ) {
					return array(
						'id'            => self::$slug,
						'title'         => self::$title,
						'description'   => sprintf(
							__( 'Memory limit is only %s. This may cause issues with large operations. Increase to at least 128M.', 'wpshadow' ),
							$memory_limit
						),
						'severity'      => 'medium',
						'threat_level'  => 45,
						'auto_fixable'  => false,
						'kb_link'       => 'https://wpshadow.com/kb/memory-limit-per-request-not-tracked',
					);
				}
			}
		}

		return null;
	}
}
