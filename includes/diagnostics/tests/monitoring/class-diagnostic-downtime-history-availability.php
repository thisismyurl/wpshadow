<?php
/**
 * Downtime History & Availability Diagnostic
 *
 * Evaluates uptime history data and calculates availability percentage.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Downtime History & Availability Diagnostic Class
 *
 * Validates availability data and flags poor uptime trends.
 *
 * @since 1.6035.0900
 */
class Diagnostic_Downtime_History_Availability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'downtime-history-availability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Downtime History & Availability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Calculates availability percentage and downtime trends';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.0900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$monitoring_plugins = array(
			'jetpack/jetpack.php'                   => 'Jetpack Monitor',
			'uptime-robot-monitor/uptime-robot.php' => 'UptimeRobot',
			'wp-downtime-monitor/wp-downtime.php'   => 'WP Downtime Monitor',
			'managewp-worker/managewp-worker.php'  => 'ManageWP',
		);

		$active_plugin = self::get_first_active_plugin( $monitoring_plugins );
		$custom_monitoring = (bool) get_option( 'wpshadow_uptime_monitoring_enabled', false );
		$history = get_option( 'wpshadow_uptime_history', array() );

		if ( ! $active_plugin && ! $custom_monitoring ) {
			return null;
		}

		if ( empty( $history ) || ! is_array( $history ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Uptime monitoring is active but no downtime history is available. Ensure your monitoring service retains availability data.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/downtime-history-availability',
				'meta'         => array(
					'active_plugin' => $active_plugin,
				),
			);
		}

		$minutes_30d = (int) ( $history['downtime_30d'] ?? 0 );
		$minutes_7d  = (int) ( $history['downtime_7d'] ?? 0 );
		$minutes_24h = (int) ( $history['downtime_24h'] ?? 0 );
		$incidents   = (int) ( $history['incidents_30d'] ?? 0 );

		$total_minutes = 30 * 24 * 60;
		$availability = $total_minutes > 0 ? ( ( $total_minutes - $minutes_30d ) / $total_minutes ) * 100 : 100;
		$availability = round( $availability, 3 );

		if ( $availability < 99.0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: availability percentage, 2: downtime minutes */
					__( 'Availability is %.3f%% over the last 30 days with %d minutes of downtime. This is below typical SLA targets.', 'wpshadow' ),
					$availability,
					$minutes_30d
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/downtime-history-availability',
				'meta'         => array(
					'availability' => $availability,
					'downtime_30d' => $minutes_30d,
					'downtime_7d'  => $minutes_7d,
					'downtime_24h' => $minutes_24h,
					'incidents'    => $incidents,
				),
			);
		}

		if ( $availability < 99.9 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: availability percentage */
					__( 'Availability is %.3f%% over the last 30 days. Consider reviewing recurring downtime causes to reach 99.9%% uptime.', 'wpshadow' ),
					$availability
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/downtime-history-availability',
				'meta'         => array(
					'availability' => $availability,
					'downtime_30d' => $minutes_30d,
					'incidents'    => $incidents,
				),
			);
		}

		return null;
	}

	/**
	 * Get the first active plugin from a list.
	 *
	 * @since  1.6035.0900
	 * @param  array $plugins Plugin list (file => label).
	 * @return string|null Active plugin label or null.
	 */
	private static function get_first_active_plugin( array $plugins ): ?string {
		foreach ( $plugins as $plugin => $label ) {
			if ( is_plugin_active( $plugin ) ) {
				return $label;
			}
		}

		return null;
	}
}
