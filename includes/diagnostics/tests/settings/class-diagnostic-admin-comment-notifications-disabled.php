<?php
/**
 * Admin Comment Notifications Disabled Diagnostic
 *
 * Checks if admin comment notifications are disabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1331
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Comment Notifications Disabled Diagnostic Class
 *
 * Detects disabled admin comment notifications.
 *
 * @since 1.5049.1331
 */
class Diagnostic_Admin_Comment_Notifications_Disabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-comment-notifications-disabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Comment Notifications Disabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin comment notifications are disabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1331
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'comments_notify' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Admin comment notification emails are disabled. This can delay moderation and responses.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-comment-notifications-disabled',
			);
		}

		return null;
	}
}
