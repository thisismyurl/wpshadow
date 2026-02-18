<?php
/**
 * Downtime Alert Status Diagnostic
 *
 * Checks whether downtime alerts are configured for uptime monitoring.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1425
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Downtime_Alert_Status Class
 *
 * Ensures downtime alerting is configured.
 *
 * @since 1.6035.1425
 */
class Diagnostic_Downtime_Alert_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'downtime-alert-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Downtime Alert Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if downtime alerts are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1425
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$alerts_enabled = (bool) get_option( 'wpshadow_uptime_alerts_enabled', false );
		$alert_email    = get_option( 'wpshadow_uptime_alert_email', '' );

		if ( ! $alerts_enabled || empty( $alert_email ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Downtime alerts are not configured. Set up alerts to respond quickly to outages.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/downtime-alert-status',
				'meta'         => array(
					'alerts_enabled' => $alerts_enabled,
					'alert_email'    => $alert_email,
				),
			);
		}

		return null;
	}
}