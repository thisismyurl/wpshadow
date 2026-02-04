<?php
/**
 * Uptime Monitoring Status Diagnostic
 *
 * Checks if uptime monitoring is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1550
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uptime Monitoring Status Diagnostic Class
 *
 * Verifies uptime monitoring is configured.
 *
 * @since 1.6035.1550
 */
class Diagnostic_Uptime_Monitoring_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uptime-monitoring-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Uptime Monitoring Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if uptime monitoring is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'downtime-prevention';

	/**
	 * Run the uptime monitoring diagnostic check.
	 *
	 * @since  1.6035.1550
	 * @return array|null Finding array if monitoring not configured, null otherwise.
	 */
	public static function check() {
		$has_monitoring = self::check_uptime_monitoring_services();

		if ( ! $has_monitoring ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No uptime monitoring detected. Enable monitoring to receive alerts when your site goes down.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/setup-uptime-monitoring',
			);
		}

		return null;
	}

	/**
	 * Check for configured uptime monitoring services.
	 *
	 * @since  1.6035.1550
	 * @return bool True if monitoring configured.
	 */
	private static function check_uptime_monitoring_services(): bool {
		// Check common monitoring plugins.
		$monitoring_plugins = array(
			'wpremote/wpremote.php',
			'mainwp-child/mainwp-child.php',
			'sitelock/sitelock.php',
		);

		foreach ( $monitoring_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check if Jetpack is active (includes monitoring).
		if ( is_plugin_active( 'jetpack/jetpack.php' ) ) {
			$jetpack_options = get_option( 'jetpack_options' );
			if ( $jetpack_options && is_array( $jetpack_options ) && isset( $jetpack_options['monitor'] ) && $jetpack_options['monitor'] ) {
				return true;
			}
		}

		// Check for custom monitoring options.
		$custom_monitoring = get_option( 'wpshadow_uptime_monitoring_enabled' );
		if ( $custom_monitoring ) {
			return true;
		}

		return false;
	}
}
