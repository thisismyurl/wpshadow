<?php
/**
 * SMTP Configuration Diagnostic
 *
 * Checks if SMTP email delivery is properly configured.
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
 * SMTP Configuration Diagnostic Class
 *
 * Verifies that SMTP (email server settings) is configured properly to ensure
 * reliable email delivery. Without SMTP, WordPress emails often end up in spam
 * or fail to send completely.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Smtp_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'smtp-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SMTP Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SMTP email delivery is properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the SMTP configuration diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if SMTP issues detected, null otherwise.
	 */
	public static function check() {
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php'                     => 'WP Mail SMTP',
			'easy-wp-smtp/easy-wp-smtp.php'                     => 'Easy WP SMTP',
			'post-smtp/postman-smtp.php'                        => 'Post SMTP',
			'wp-ses/wp-ses.php'                                 => 'WP SES',
			'sendgrid-email-delivery-simplified/wpsendgrid.php' => 'SendGrid',
		);

		$smtp_configured   = false;
		$configured_plugin = '';

		// Check for SMTP plugins.
		foreach ( $smtp_plugins as $plugin_path => $name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$smtp_configured   = true;
				$configured_plugin = $name;
				break;
			}
		}

		// Check for SMTP constants in wp-config.php.
		if ( ! $smtp_configured ) {
			if ( defined( 'SMTP_HOST' ) || defined( 'WPMS_SMTP_HOST' ) || defined( 'WPMS_ON' ) ) {
				$smtp_configured   = true;
				$configured_plugin = __( 'wp-config.php constants', 'wpshadow' );
			}
		}

		// Check if phpmailer is customized via filters.
		if ( ! $smtp_configured && has_action( 'phpmailer_init' ) ) {
			$smtp_configured   = true;
			$configured_plugin = __( 'Custom phpmailer filter', 'wpshadow' );
		}

		// If SMTP is not configured, return finding.
		if ( ! $smtp_configured ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site is using basic email sending (like mailing a letter without a return address). Adding an SMTP service (think of it as a professional mail carrier) helps ensure your emails reach inboxes instead of spam folders. Popular services include SendGrid, Mailgun, or Amazon SES.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/smtp-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'smtp_configured'   => false,
					'recommended_plugins' => array_values( $smtp_plugins ),
				),
			);
		}

		// SMTP is configured - optionally check configuration health.
		$warnings = array();

		// Check if emails are actually being sent.
		$test_email_option = get_transient( 'wpshadow_smtp_test_last_result' );
		if ( false !== $test_email_option && 'failed' === $test_email_option ) {
			$warnings[] = __( 'Last SMTP test email failed - check credentials', 'wpshadow' );
		}

		// If there are warnings, return informational finding.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug . '-warnings',
				'title'        => __( 'SMTP Configuration Warnings', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: configured SMTP plugin name */
					__( 'SMTP is configured via %s but there are some warnings.', 'wpshadow' ),
					$configured_plugin
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/smtp-troubleshooting?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'smtp_configured'   => true,
					'configured_via'    => $configured_plugin,
					'warnings'          => $warnings,
				),
			);
		}

		return null; // SMTP configured and healthy.
	}
}
