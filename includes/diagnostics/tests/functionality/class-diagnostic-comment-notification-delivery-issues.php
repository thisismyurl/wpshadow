<?php
/**
 * Comment Notification Delivery Issues Diagnostic
 *
 * Checks if comment notifications are being delivered properly.
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
 * Comment Notification Delivery Issues Diagnostic Class
 *
 * Detects problems with comment notification delivery.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Comment_Notification_Delivery_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-notification-delivery-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Notification Delivery Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for notification delivery problems';

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
		// Check if mail function is available
		if ( ! function_exists( 'wp_mail' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WordPress mail function is not available. Comment notifications may not be sent.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-notification-delivery-issues',
			);
		}

		// Check if admin email is configured
		$admin_email = get_option( 'admin_email', '' );
		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Admin email is not valid. Comment notifications cannot be sent.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-notification-delivery-issues',
			);
		}

		// Check if comments are disabled site-wide
		if ( 'closed' === get_option( 'default_comment_status' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comments are disabled by default. Comment notifications will not be generated.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-notification-delivery-issues',
			);
		}

		return null;
	}
}
