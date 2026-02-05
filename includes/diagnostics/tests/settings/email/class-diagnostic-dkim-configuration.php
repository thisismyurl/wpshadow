<?php
/**
 * DKIM Configuration Diagnostic
 *
 * Checks if DKIM (DomainKeys Identified Mail) is properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1530
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DKIM Configuration Diagnostic Class
 *
 * Verifies that DKIM signing is configured. DKIM adds a digital signature
 * to your emails (like a wax seal on a letter) proving they haven't been
 * tampered with and genuinely came from your domain.
 *
 * @since 1.6035.1530
 */
class Diagnostic_Dkim_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dkim-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DKIM Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if DKIM email signing is properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the DKIM configuration diagnostic check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if DKIM issues detected, null otherwise.
	 */
	public static function check() {
		$dkim_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php' => 'WP Mail SMTP (with provider support)',
			'post-smtp/postman-smtp.php'    => 'Post SMTP',
		);

		$dkim_configured   = false;
		$configured_via    = '';

		// Check if using email service with built-in DKIM.
		$email_services = array(
			'defined' => array(
				'SENDGRID_API_KEY'    => 'SendGrid',
				'MAILGUN_API_KEY'     => 'Mailgun',
				'AWS_SES_ACCESS_KEY'  => 'Amazon SES',
				'POSTMARK_API_TOKEN'  => 'Postmark',
			),
			'plugins' => array(
				'sendgrid-email-delivery-simplified/wpsendgrid.php' => 'SendGrid',
				'mailgun/mailgun.php'                               => 'Mailgun',
				'wp-ses/wp-ses.php'                                 => 'Amazon SES',
			),
		);

		// Check for service constants.
		foreach ( $email_services['defined'] as $constant => $service_name ) {
			if ( defined( $constant ) ) {
				$dkim_configured = true;
				$configured_via  = $service_name . ' (built-in DKIM)';
				break;
			}
		}

		// Check for service plugins.
		if ( ! $dkim_configured ) {
			foreach ( $email_services['plugins'] as $plugin_path => $service_name ) {
				if ( is_plugin_active( $plugin_path ) ) {
					$dkim_configured = true;
					$configured_via  = $service_name . ' (built-in DKIM)';
					break;
				}
			}
		}

		// Check for manual DKIM configuration.
		if ( ! $dkim_configured ) {
			// Check if DKIM keys are defined.
			if ( defined( 'DKIM_PRIVATE_KEY' ) || get_option( 'wpshadow_dkim_private_key' ) ) {
				$dkim_configured = true;
				$configured_via  = __( 'Manual DKIM configuration', 'wpshadow' );
			}
		}

		if ( ! $dkim_configured ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Adding DKIM email signing adds a digital signature to your emails (like a tamper-proof wax seal on a letter). This proves to email providers that your messages are authentic and haven\'t been modified in transit. Most email services like SendGrid, Mailgun, or Amazon SES include this automatically.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dkim-configuration',
				'context'      => array(
					'dkim_configured'  => false,
					'recommended_services' => array( 'SendGrid', 'Mailgun', 'Amazon SES', 'Postmark' ),
				),
			);
		}

		// DKIM is configured.
		return null;
	}
}
