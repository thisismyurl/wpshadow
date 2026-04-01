<?php
/**
 * Notification Delivery System Not Configured Diagnostic
 *
 * Checks if notification delivery is configured.
 *
 * @package    WPShadow
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
 * Notification Delivery System Not Configured Diagnostic Class
 *
 * Detects missing notification delivery.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Notification_Delivery_System_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'notification-delivery-system-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Notification Delivery System Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if notification delivery is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for notification service integration
		$notification_enabled = get_option( 'notification_service_enabled', null );
		if ( null === $notification_enabled ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Notification delivery system is not configured. Set up email, SMS, or webhook notifications for critical alerts, system events, and user activities.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/notification-delivery-system-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
