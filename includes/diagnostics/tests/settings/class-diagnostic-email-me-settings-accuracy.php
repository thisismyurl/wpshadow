<?php
/**
 * Email Me Settings Accuracy Diagnostic
 *
 * Validates "Email me when" settings work correctly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.1912
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Me Settings Accuracy Diagnostic Class
 *
 * Checks if email notification settings are properly configured.
 *
 * @since 1.2601.1912
 */
class Diagnostic_Email_Me_Settings_Accuracy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-me-settings-accuracy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Me Settings Accuracy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates "Email me when" settings work correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.1912
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check comments_notify option (email on new comment).
		$comments_notify = get_option( 'comments_notify', '0' );

		// Check moderation_notify option (email on moderation).
		$moderation_notify = get_option( 'moderation_notify', '1' );

		// Check admin email.
		$admin_email = get_option( 'admin_email', '' );
		if ( empty( $admin_email ) ) {
			$issues[] = __( 'Admin email address is not configured', 'wpshadow' );
		} elseif ( ! is_email( $admin_email ) ) {
			$issues[] = sprintf(
				/* translators: %s: email address */
				__( 'Admin email address is invalid: %s', 'wpshadow' ),
				esc_html( $admin_email )
			);
		}

		// Check if both notifications are disabled.
		if ( ( '0' === $comments_notify || 0 === $comments_notify ) &&
			( '0' === $moderation_notify || 0 === $moderation_notify ) ) {
			$issues[] = __( 'Both comment and moderation email notifications are disabled', 'wpshadow' );
		}

		// Check if comments are enabled.
		$default_comment_status = get_option( 'default_comment_status', 'open' );
		if ( 'closed' === $default_comment_status || 'close' === $default_comment_status ) {
			// Comments are disabled, notifications are not relevant.
			return null;
		}

		// Check if comment moderation is enabled but moderation_notify is off.
		$comment_moderation = get_option( 'comment_moderation', '0' );
		if ( ( '1' === $comment_moderation || 1 === $comment_moderation ) &&
			( '0' === $moderation_notify || 0 === $moderation_notify ) ) {
			$issues[] = __( 'Comment moderation is enabled but moderation notifications are disabled', 'wpshadow' );
		}

		// Check if email functionality might be broken (check for common issues).
		// We can't actually test wp_mail() here without sending a test email,
		// but we can check for known problematic configurations.
		$mail_from = get_option( 'mail_from', '' );
		if ( ! empty( $mail_from ) && ! is_email( $mail_from ) ) {
			$issues[] = __( 'Custom mail "From" address is configured but invalid', 'wpshadow' );
		}

		// Check for active SMTP plugins (which can affect email delivery).
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php',
			'easy-wp-smtp/easy-wp-smtp.php',
			'post-smtp/postman-smtp.php',
		);
		$has_smtp     = false;
		foreach ( $smtp_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_smtp = true;
				break;
			}
		}

		// Check mailserver settings (usually not configured in modern WordPress).
		$mailserver_url = get_option( 'mailserver_url', '' );
		if ( ! empty( $mailserver_url ) && ! $has_smtp ) {
			$issues[] = __( 'Legacy mailserver settings detected - may not work with modern hosting', 'wpshadow' );
		}

		// If notification is enabled but email is invalid, that's a critical issue.
		if ( ( '1' === $comments_notify || '1' === $moderation_notify ) &&
			( empty( $admin_email ) || ! is_email( $admin_email ) ) ) {
			$issues[] = __( 'Email notifications are enabled but admin email is invalid or missing', 'wpshadow' );
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d email notification configuration issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'medium',
			'threat_level'       => 50,
			'site_health_status' => 'recommended',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/email-me-settings-accuracy',
			'family'             => self::$family,
			'details'            => array(
				'issues'            => $issues,
				'comments_notify'   => $comments_notify,
				'moderation_notify' => $moderation_notify,
				'admin_email'       => $admin_email,
				'admin_email_valid' => ! empty( $admin_email ) && is_email( $admin_email ),
				'has_smtp_plugin'   => $has_smtp,
			),
		);
	}
}
