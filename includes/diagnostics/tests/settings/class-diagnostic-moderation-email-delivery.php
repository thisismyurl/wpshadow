<?php
/**
 * Moderation Email Delivery Diagnostic
 *
 * Verifies moderation notification emails are properly configured.
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
 * Moderation Email Delivery Diagnostic Class
 *
 * Checks moderation email notification delivery.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Moderation_Email_Delivery extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'moderation-email-delivery';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Moderation Email Delivery';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies moderation notification email delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if moderation is enabled.
		$comment_moderation = get_option( 'comment_moderation', 0 );
		if ( ! $comment_moderation ) {
			return null; // Moderation disabled.
		}

		// Check moderation notification setting.
		$moderation_notify = get_option( 'moderation_notify', 1 );
		if ( ! $moderation_notify ) {
			$issues[] = __( 'Moderation email notifications disabled', 'wpshadow' );
		}

		// Check admin email.
		$admin_email = get_option( 'admin_email' );
		if ( ! is_email( $admin_email ) ) {
			$issues[] = __( 'Admin email is invalid - notifications cannot be delivered', 'wpshadow' );
		}

		// Check moderation threshold settings.
		$comment_max_links = (int) get_option( 'comment_max_links', 2 );
		$disallowed_keys = get_option( 'disallowed_keys', '' );

		if ( ! $disallowed_keys && $comment_max_links <= 2 ) {
			$issues[] = __( 'Minimal moderation rules - few comments will require admin approval', 'wpshadow' );
		}

		// Check mail delivery history.
		$mail_errors = get_transient( 'wpshadow_mail_errors' );
		if ( $mail_errors ) {
			$issues[] = sprintf(
				/* translators: %s: recent error count */
				__( 'Recent email delivery errors detected - %s failures', 'wpshadow' ),
				$mail_errors
			);
		}

		// Check SMTP configuration.
		if ( ! is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) ) {
			$issues[] = __( 'Using default PHP mail() without SMTP plugin - reliability concerns', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/moderation-email-delivery',
			);
		}

		return null;
	}
}
