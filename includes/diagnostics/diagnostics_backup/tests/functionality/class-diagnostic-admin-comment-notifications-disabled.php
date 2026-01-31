<?php
/**
 * Admin Comment Notifications Disabled Diagnostic
 *
 * Checks if admin is receiving comment notifications.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
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
 * Detects if admin comment notifications are enabled.
 *
 * @since 1.2601.2310
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
	protected static $description = 'Checks if admins receive comment notifications';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if admin notifications are enabled
		$comment_notification = get_option( 'comments_notify', 1 );

		if ( ! $comment_notification ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Admin notifications for new comments are disabled. Site admins will not be notified when comments are posted.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-comment-notifications-disabled',
			);
		}

		// Check if moderator notifications are enabled
		$moderation_notify = get_option( 'moderation_notify', 1 );

		if ( ! $moderation_notify ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Moderator notifications for pending comments are disabled. Moderators will not know when comments need approval.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-comment-notifications-disabled',
			);
		}

		return null;
	}
}
