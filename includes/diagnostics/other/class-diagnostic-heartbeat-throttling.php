<?php
declare(strict_types=1);
/**
 * Heartbeat Throttling Diagnostic
 *
 * Philosophy: Educate on reducing admin-ajax load for better performance.
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if WordPress Heartbeat is throttled.
 */
class Diagnostic_Heartbeat_Throttling extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// If constant is defined to disable heartbeat
		if ( defined( 'WP_DISABLE_HEARTBEAT' ) && WP_DISABLE_HEARTBEAT ) {
			return null; // Already disabled/throttled
		}
		// Check if heartbeat is throttled via filters
		// `heartbeat_settings` or `heartbeat_send` filters indicate custom intervals
		if ( has_filter( 'heartbeat_settings' ) || has_filter( 'heartbeat_send' ) ) {
			return null; // Considered throttled/customized
		}

		return array(
			'title'        => 'WordPress Heartbeat Not Throttled',
			'description'  => 'Heartbeat API runs frequently in wp-admin. Throttling reduces CPU and AJAX load, improving performance.',
			'severity'     => 'low',
			'category'     => 'performance',
			'kb_link'      => 'https://wpshadow.com/kb/throttle-wordpress-heartbeat/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=heartbeat',
			'auto_fixable' => false,
			'threat_level' => 25,
		);
	}
}
