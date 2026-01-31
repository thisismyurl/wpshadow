<?php
/**
 * Site Uptime Monitoring Not Configured Diagnostic
 *
 * Checks if site uptime is monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2325
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Uptime Monitoring Not Configured Diagnostic Class
 *
 * Detects missing uptime monitoring.
 *
 * @since 1.2601.2325
 */
class Diagnostic_Site_Uptime_Monitoring_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-uptime-monitoring-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Uptime Monitoring Not Configured';

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
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2325
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for uptime monitoring plugins
		$monitoring_plugins = array(
			'uptime-monitor/uptime-monitor.php',
			'site-health-check/site-health-check.php',
		);

		$monitoring_active = false;
		foreach ( $monitoring_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$monitoring_active = true;
				break;
			}
		}

		if ( ! $monitoring_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Site uptime is not being monitored. Set up external monitoring to be alerted when your site goes down.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/site-uptime-monitoring-not-configured',
			);
		}

		return null;
	}
}
