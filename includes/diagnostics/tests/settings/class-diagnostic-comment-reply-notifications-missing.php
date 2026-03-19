<?php
/**
 * Comment Reply Notifications Missing Diagnostic
 *
 * Checks if reply notification emails are unavailable for threaded comments.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
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
 * Detects missing reply notification support for threaded comments.
 *
 * @since 1.6093.1200
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
	protected static $description = 'Checks if reply notifications are available for threaded comments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'thread_comments' ) ) {
			return null;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_plugins = get_option( 'active_plugins', array() );
		$subscription_plugins = array(
			'subscribe-to-comments/subscribe-to-comments.php',
			'subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php',
		);

		$active_subscription = false;
		foreach ( $subscription_plugins as $plugin_file ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				$active_subscription = true;
				break;
			}
		}

		if ( ! $active_subscription ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Threaded comments are enabled, but reply notification emails are not configured. Consider enabling subscriptions so commenters can follow replies.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-reply-notifications-missing',
			);
		}

		return null;
	}
}
