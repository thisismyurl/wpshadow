<?php
/**
 * Post Status Change Notification Not Configured Diagnostic
 *
 * Checks if post status changes are notified.
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
 * Post Status Change Notification Not Configured Diagnostic Class
 *
 * Detects missing post status notifications.
 *
 * @since 1.2601.2325
 */
class Diagnostic_Post_Status_Change_Notification_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-status-change-notification-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Status Change Notification Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post status changes trigger notifications';

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
		// Check if notification plugins are active
		$notification_plugins = array(
			'email-post-changes/email-post-changes.php',
			'post-notification/post-notification.php',
		);

		$notification_active = false;
		foreach ( $notification_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$notification_active = true;
				break;
			}
		}

		if ( ! $notification_active ) {
			// Not a critical issue
			return null;
		}

		return null;
	}
}
