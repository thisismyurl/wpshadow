<?php
/**
 * Comment Thread Email Notifications Treatment
 *
 * Checks if thread email notifications are available for comments.
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
 * Comment Thread Email Notifications Treatment Class
 *
 * Detects missing thread notification support for commenters.
 *
 * @since 1.5049.1331
 */
class Treatment_Comment_Thread_Email_Notifications extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-thread-email-notifications';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Thread Email Notifications';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment thread email notifications are available';

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
				'description'  => __( 'Threaded comments are enabled, but reply notification emails are not configured. Consider enabling comment subscription notifications.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'details'      => array(
					'threaded_comments' => true,
					'subscription_plugin_active' => false,
				),
				'kb_link'      => 'https://wpshadow.com/kb/comment-thread-email-notifications',
			);
		}

		return null;
	}
}
