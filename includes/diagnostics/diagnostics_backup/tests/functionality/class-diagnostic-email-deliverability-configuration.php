<?php
/**
 * Email Deliverability Configuration Diagnostic
 *
 * Checks if email deliverability is properly configured.
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
 * Email Deliverability Configuration Diagnostic Class
 *
 * Detects email delivery issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Email_Deliverability_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-deliverability-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Deliverability Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if email delivery is properly configured';

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
		// Check for mail plugins
		$mail_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php',
			'sendgrid-email-delivery-simplified/sendgrid-email-delivery-simplified.php',
			'mailgun/mailgun.php',
		);

		$mail_active = false;
		foreach ( $mail_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$mail_active = true;
				break;
			}
		}

		if ( ! $mail_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No email delivery service is configured. WordPress emails will fail to send or be marked as spam.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/email-deliverability-configuration',
			);
		}

		return null;
	}
}
