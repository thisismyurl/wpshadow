<?php
/**
 * Comment Notification Delivery Issues Diagnostic
 *
 * Checks for missing configuration that can affect comment email delivery.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
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
 * Detects potential mail delivery configuration issues.
 *
 * @since 0.6093.1200
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
	protected static $description = 'Checks for potential delivery issues with comment notifications';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'comments_notify' ) ) {
			return null;
		}

		$issues = array();

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_plugins = get_option( 'active_plugins', array() );
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php',
			'post-smtp/postman-smtp.php',
			'easy-wp-smtp/easy-wp-smtp.php',
		);

		$has_smtp = false;
		foreach ( $smtp_plugins as $plugin_file ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				$has_smtp = true;
				break;
			}
		}

		if ( ! $has_smtp ) {
			$issues[] = __( 'No SMTP plugin detected. Transactional emails can fail or be flagged without proper SMTP configuration.', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Comment notification emails may not be reliably delivered.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'details'      => array(
				'issues' => $issues,
			),
			'kb_link'      => 'https://wpshadow.com/kb/comment-notification-delivery-issues?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
