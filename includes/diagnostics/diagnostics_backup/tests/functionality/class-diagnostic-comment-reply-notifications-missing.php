<?php
/**
 * Comment Reply Notifications Missing Diagnostic
 *
 * Checks if users are notified of replies to their comments.
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
 * Comment Reply Notifications Missing Diagnostic Class
 *
 * Detects if reply notifications are configured.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Comment_Reply_Notifications_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-reply-notifications-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Reply Notifications Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if reply notifications are enabled';

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
		// Check if threaded comments are enabled
		$thread_comments = get_option( 'thread_comments', 0 );

		if ( ! $thread_comments ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Threaded comments are disabled. Users will not be notified of replies to their comments.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-reply-notifications-missing',
			);
		}

		// Check if threaded comments are properly configured
		$thread_comments_depth = get_option( 'thread_comments_depth', 5 );
		if ( $thread_comments_depth < 2 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comment threading depth is too shallow. Reply notification chains may not work properly.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-reply-notifications-missing',
			);
		}

		return null;
	}
}
