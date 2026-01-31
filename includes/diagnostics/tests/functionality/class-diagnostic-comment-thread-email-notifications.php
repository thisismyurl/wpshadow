<?php
/**
 * Comment Thread Email Notifications Diagnostic
 *
 * Checks if threaded comment email notifications work properly.
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
 * Comment Thread Email Notifications Diagnostic Class
 *
 * Detects problems with threaded comment notifications.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Comment_Thread_Email_Notifications extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-thread-email-notifications';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Thread Email Notifications';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks threaded comment notification setup';

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
		// Check if threading is enabled
		$thread_comments = get_option( 'thread_comments', 0 );
		$depth            = get_option( 'thread_comments_depth', 5 );

		if ( ! $thread_comments ) {
			return null; // Threading disabled is not an issue for notifications
		}

		// Check if comments require approval
		$require_name_email = get_option( 'require_name_email', 0 );

		if ( ! $require_name_email ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Threaded comment notifications are enabled with depth %d, but name/email not required. Reply notifications may not reach commenters.', 'wpshadow' ),
					absint( $depth )
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-thread-email-notifications',
			);
		}

		return null;
	}
}
