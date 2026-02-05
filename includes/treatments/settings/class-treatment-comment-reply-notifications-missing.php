<?php
/**
 * Comment Reply Notifications Missing Treatment
 *
 * Checks if reply notification emails are unavailable for threaded comments.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1331
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Reply Notifications Missing Treatment Class
 *
 * Detects missing reply notification support for threaded comments.
 *
 * @since 1.5049.1331
 */
class Treatment_Comment_Reply_Notifications_Missing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-reply-notifications-missing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Reply Notifications Missing';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if reply notifications are available for threaded comments';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1331
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
