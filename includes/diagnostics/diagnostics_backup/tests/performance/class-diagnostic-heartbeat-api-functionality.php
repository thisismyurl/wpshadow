<?php
/**
 * Diagnostic: Heartbeat API Functionality
 *
 * Checks WordPress Heartbeat API configuration and performance.
 * Excessive heartbeat requests can impact server performance.
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
 * Class Diagnostic_Heartbeat_Api_Functionality
 *
 * Monitors WordPress Heartbeat API settings.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Heartbeat_Api_Functionality extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'heartbeat-api-functionality';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Heartbeat API Functionality';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks WordPress Heartbeat API configuration';

	/**
	 * Check Heartbeat API settings.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if heartbeat is completely disabled.
		$heartbeat_disabled = has_filter( 'heartbeat_settings', '__return_false' ) ||
							wp_script_is( 'heartbeat', 'registered' ) === false;

		// Get current heartbeat settings.
		$settings = apply_filters( 'heartbeat_settings', array() );

		// Default interval is 15 seconds for dashboard, 60 for frontend.
		$default_interval = is_admin() ? 15 : 60;
		$interval         = isset( $settings['interval'] ) ? (int) $settings['interval'] : $default_interval;

		// Warn if heartbeat is running too frequently (< 30 seconds).
		if ( ! $heartbeat_disabled && $interval < 30 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Heartbeat interval in seconds */
					__( 'Heartbeat API is running every %d seconds. Consider increasing interval to reduce server load.', 'wpshadow' ),
					$interval
				),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/heartbeat_api_functionality',
				'meta'        => array(
					'interval' => $interval,
					'settings' => $settings,
				),
			);
		}

		// Informational: Heartbeat is disabled.
		if ( $heartbeat_disabled ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Heartbeat API is disabled. This may affect autosave, post locking, and other real-time features.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/heartbeat_api_functionality',
				'meta'        => array(
					'disabled' => true,
				),
			);
		}

		// Heartbeat is configured appropriately.
		return null;
	}
}
