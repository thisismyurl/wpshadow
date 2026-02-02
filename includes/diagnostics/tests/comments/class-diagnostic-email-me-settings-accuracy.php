<?php
/**
 * Email Me Settings Accuracy Diagnostic
 *
 * Verifies comment notification email settings are properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1755
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
 * Checks comment email notification configuration.
 *
 * @since 1.26032.1755
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
	protected static $description = 'Verifies comment email notification settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check email notification settings.
		$comments_notify = get_option( 'comments_notify', 0 );
		$moderation_notify = get_option( 'moderation_notify', 1 );

		// Get admin email.
		$admin_email = get_option( 'admin_email' );

		// Verify admin email is valid.
		if ( ! is_email( $admin_email ) ) {
			$issues[] = __( 'Admin email is not valid - notifications will not be delivered', 'wpshadow' );
		}

		// Check if both notifications are disabled.
		if ( ! $comments_notify && ! $moderation_notify ) {
			$issues[] = __( 'All comment email notifications disabled - may miss important comments', 'wpshadow' );
		}

		// Check moderation queue size if notifications enabled.
		if ( $moderation_notify ) {
			$pending_count = wp_count_comments();
			if ( isset( $pending_count->moderated ) && $pending_count->moderated > 50 ) {
				$issues[] = sprintf(
					/* translators: %d: pending comments */
					__( 'Email notifications enabled but large moderation queue (%d comments) - may overwhelm inbox', 'wpshadow' ),
					$pending_count->moderated
				);
			}
		}

		// Check if email delivery is working.
		$mail_test_option = get_transient( 'wpshadow_mail_test_result' );
		if ( false === $mail_test_option ) {
			// Run a quick test.
			$test_result = wp_mail( $admin_email, 'Test', 'Test', array(), array() );
			set_transient( 'wpshadow_mail_test_result', $test_result ? 'success' : 'failure', DAY_IN_SECONDS );
			
			if ( ! $test_result ) {
				$issues[] = __( 'Email delivery test failed - notifications may not be sent', 'wpshadow' );
			}
		} elseif ( 'failure' === $mail_test_option ) {
			$issues[] = __( 'Previous email delivery test failed - notifications may not be sent', 'wpshadow' );
		}

		// Check for SMTP plugin.
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php',
			'easy-wp-smtp/easy-wp-smtp.php',
			'post-smtp/postman-smtp.php',
		);

		$has_smtp = false;
		foreach ( $smtp_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_smtp = true;
				break;
			}
		}

		if ( ! $has_smtp && ( $comments_notify || $moderation_notify ) ) {
			$issues[] = __( 'Using default PHP mail() which is less reliable than SMTP', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-me-settings-accuracy',
			);
		}

		return null;
	}
}
