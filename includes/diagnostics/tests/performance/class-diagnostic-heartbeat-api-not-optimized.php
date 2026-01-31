<?php
/**
 * Heartbeat API Not Optimized Diagnostic
 *
 * Checks if Heartbeat API is optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Heartbeat API Not Optimized Diagnostic Class
 *
 * Detects unoptimized Heartbeat.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Heartbeat_API_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'heartbeat-api-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Heartbeat API Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Heartbeat API is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Heartbeat is configured
		if ( ! has_filter( 'heartbeat_settings', 'optimize_heartbeat' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Heartbeat API is not optimized. Disable Heartbeat on frontend to reduce server load and AJAX requests.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/heartbeat-api-not-optimized',
			);
		}

		return null;
	}
}
