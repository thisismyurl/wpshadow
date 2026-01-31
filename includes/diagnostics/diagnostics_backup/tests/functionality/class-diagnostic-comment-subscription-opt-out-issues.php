<?php
/**
 * Comment Subscription Opt-Out Issues Diagnostic
 *
 * Checks if comment subscribers can opt-out properly.
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
 * Comment Subscription Opt-Out Issues Diagnostic Class
 *
 * Detects problems with comment subscription opt-out.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Comment_Subscription_Opt_Out_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-subscription-opt-out-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Subscription Opt-Out Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if users can unsubscribe from notifications';

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
		// Check if comment subscription plugins exist
		$has_subscription_plugin = false;

		$plugins_to_check = array(
			'subscribe-to-comments-reloaded/subscribe-to-comments.php',
			'comment-reply-notification/comment-reply-notification.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
		);

		foreach ( $plugins_to_check as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_subscription_plugin = true;
				break;
			}
		}

		// Check if threaded comments are enabled (default WordPress way)
		$thread_comments = get_option( 'thread_comments', 0 );

		if ( $thread_comments && ! $has_subscription_plugin ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Threaded comments are enabled but no subscription plugin is active. Users may not be able to unsubscribe from reply notifications.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-subscription-opt-out-issues',
			);
		}

		return null;
	}
}
