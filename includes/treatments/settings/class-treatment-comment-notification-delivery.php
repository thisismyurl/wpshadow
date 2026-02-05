<?php
/**
 * Comment Notification Delivery Treatment
 *
 * Verifies comment notification emails are being delivered to users.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Notification Delivery Treatment Class
 *
 * Checks comment notification email delivery to users.
 *
 * @since 1.6032.1900
 */
class Treatment_Comment_Notification_Delivery extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-notification-delivery';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Notification Delivery';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment notification email delivery to users';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if comment notifications are enabled.
		$comments_notify = get_option( 'comments_notify', 0 );
		if ( ! $comments_notify ) {
			$issues[] = __( 'Comment reply notifications disabled - users won\'t be notified of responses', 'wpshadow' );
		}

		// Check if there are subscribers.
		global $wpdb;
		$subscriber_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->users} u
			INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
			WHERE um.meta_key = '{$wpdb->prefix}capabilities'
			AND um.meta_value LIKE '%subscriber%'"
		);

		if ( $comments_notify && $subscriber_count > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of subscribers */
				__( 'Notifying %d subscribers on each comment - may overwhelm mail system', 'wpshadow' ),
				$subscriber_count
			);
		}

		// Check if mail system is working.
		$mail_failures = get_transient( 'wpshadow_notification_mail_failures' );
		if ( $mail_failures && $mail_failures > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of failures */
				__( 'Recent notification email failures (%d) - check mail configuration', 'wpshadow' ),
				$mail_failures
			);
		}

		// Check for email plugins.
		$has_email_plugin = is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) ||
			is_plugin_active( 'easy-wp-smtp/easy-wp-smtp.php' ) ||
			is_plugin_active( 'post-smtp/postman-smtp.php' );

		if ( $comments_notify && ! $has_email_plugin ) {
			$issues[] = __( 'Using default mail - consider SMTP plugin for reliability', 'wpshadow' );
		}

		// Check default comment notification setting.
		$default_comment_status = get_option( 'default_comment_status', 'open' );
		if ( $default_comment_status !== 'open' && $comments_notify ) {
			$issues[] = __( 'Comments disabled by default - notifications less relevant', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-notification-delivery',
			);
		}

		return null;
	}
}
