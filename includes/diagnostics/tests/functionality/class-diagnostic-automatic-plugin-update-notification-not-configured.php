<?php
/**
 * Automatic Plugin Update Notification Not Configured Diagnostic
 *
 * Checks if automatic plugin update notifications are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2346
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automatic Plugin Update Notification Not Configured Diagnostic Class
 *
 * Detects missing plugin update notifications.
 *
 * @since 1.2601.2346
 */
class Diagnostic_Automatic_Plugin_Update_Notification_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'automatic-plugin-update-notification-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Automatic Plugin Update Notification Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugin update notifications are enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2346
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if automatic plugin updates are disabled
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Automatic plugin update notifications are disabled. Enable notifications to stay informed of security updates.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/automatic-plugin-update-notification-not-configured',
			);
		}

		return null;
	}
}
