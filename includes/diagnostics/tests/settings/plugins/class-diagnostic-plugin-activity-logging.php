<?php
/**
 * Plugin Activity Logging Diagnostic
 *
 * Detects plugins not logging security-critical events.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1700
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Activity Logging Class
 *
 * Checks for activity logging plugins and configuration.
 * Audit trails are critical for security forensics.
 *
 * @since 1.5029.1700
 */
class Diagnostic_Plugin_Activity_Logging extends Diagnostic_Base {

	protected static $slug        = 'plugin-activity-logging';
	protected static $title       = 'Plugin Activity Logging';
	protected static $description = 'Detects missing security event logging';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_activity_logging';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Check for common activity logging plugins.
		$logging_plugins = array(
			'wp-security-audit-log/wp-security-audit-log.php' => 'WP Activity Log',
			'simple-history/index.php' => 'Simple History',
			'stream/stream.php' => 'Stream',
			'aryo-activity-log/aryo-activity-log.php' => 'Activity Log',
		);

		$has_logging = false;
		$active_logger = '';

		foreach ( $logging_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_logging = true;
				$active_logger = $plugin_name;
				break;
			}
		}

		// Check if WPShadow's own activity logger is configured.
		$wpshadow_logging = get_option( 'wpshadow_activity_logging_enabled', false );
		if ( $wpshadow_logging ) {
			$has_logging = true;
			$active_logger = 'WPShadow Activity Logger';
		}

		if ( ! $has_logging ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No activity logging detected. Enable audit trails for security monitoring.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-activity-logging',
				'data'         => array(
					'has_logging' => false,
					'suggested_plugins' => array_values( $logging_plugins ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
