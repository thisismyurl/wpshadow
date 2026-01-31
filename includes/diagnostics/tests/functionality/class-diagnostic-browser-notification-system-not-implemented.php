<?php
/**
 * Browser Notification System Not Implemented Diagnostic
 *
 * Checks if browser notifications are implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2349
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Browser Notification System Not Implemented Diagnostic Class
 *
 * Detects missing browser notifications.
 *
 * @since 1.2601.2349
 */
class Diagnostic_Browser_Notification_System_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'browser-notification-system-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Browser Notification System Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if browser notifications are implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2349
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for push notification plugins
		$notification_plugins = array(
			'one-signal/onesignal.php',
			'push-notifications-for-wordpress/push-notifications.php',
		);

		$notification_active = false;
		foreach ( $notification_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$notification_active = true;
				break;
			}
		}

		if ( ! $notification_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Browser notifications are not implemented. Add push notifications to keep users engaged and increase repeat visits.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/browser-notification-system-not-implemented',
			);
		}

		return null;
	}
}
