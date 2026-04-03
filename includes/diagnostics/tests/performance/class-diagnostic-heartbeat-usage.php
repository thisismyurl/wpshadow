<?php
/**
 * Heartbeat Usage Diagnostic
 *
 * Checks whether the WordPress Heartbeat API interval has been reviewed
 * or configured, preventing unnecessary background AJAX requests.
 *
 * @package WPShadow
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
 * Diagnostic_Heartbeat_Usage Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Heartbeat_Usage extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'heartbeat-usage';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Heartbeat Usage';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the WordPress Heartbeat API interval is controlled to prevent frequent background AJAX requests from consuming PHP workers on the server.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Detects heartbeat-control plugins first, then checks the Heartbeat
	 * interval option to flag unthrottled or high-frequency configurations.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when Heartbeat is unthrottled, null when healthy.
	 */
	public static function check() {
		// Check for known heartbeat-control plugins.
		$control_options = array(
			'heartbeat_control_settings', // Heartbeat Control (WP Rocket)
			'perfmatters_options',          // Perfmatters
			'autoptimize_settings',         // Autoptimize (Extras)
		);

		foreach ( $control_options as $opt ) {
			if ( false !== get_option( $opt, false ) ) {
				return null;
			}
		}

		// WP Rocket checks heartbeat control via its own options.
		$rocket = get_option( 'wp_rocket_settings', array() );
		if ( is_array( $rocket ) && isset( $rocket['heartbeat_admin_behavior'] ) ) {
			return null;
		}

		$interval = (int) get_option( 'heartbeat_interval', 60 );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The WordPress Heartbeat API interval has not been reviewed or controlled. The Heartbeat API sends Ajax requests to the server at regular intervals from the admin area and post editor. On busy sites or constrained hosting plans, these background requests can consume significant PHP worker capacity. Consider reviewing the interval or disabling Heartbeat on pages where it is not needed.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 15,
			'kb_link'      => 'https://wpshadow.com/kb/heartbeat-api?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'heartbeat_interval_option' => $interval,
				'note'                      => __( 'Install the Heartbeat Control plugin or configure WP Rocket / Perfmatters to manage the Heartbeat frequency.', 'wpshadow' ),
			),
		);
	}
}
