<?php
/**
 * Update Notification Delivery Not Monitored Diagnostic
 *
 * Checks if update notification delivery is monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2347
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update Notification Delivery Not Monitored Diagnostic Class
 *
 * Detects unmonitored update notifications.
 *
 * @since 1.2601.2347
 */
class Diagnostic_Update_Notification_Delivery_Not_Monitored extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'update-notification-delivery-not-monitored';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Update Notification Delivery Not Monitored';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if update notifications are monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2347
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if email notifications are configured
		$admin_email = get_option( 'admin_email' );

		if ( ! is_email( $admin_email ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Admin email is invalid. Update notification delivery cannot be monitored. Set a valid email address.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/update-notification-delivery-not-monitored',
			);
		}

		return null;
	}
}
