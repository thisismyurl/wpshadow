<?php
/**
 * SMTP Authentication Treatment
 *
 * Tests SMTP authentication configuration and validates credentials.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1735
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SMTP Authentication Treatment Class
 *
 * Verifies that SMTP authentication is properly configured. This is like
 * having a password to prove who you are - without it, email servers will
 * reject your messages.
 *
 * @since 1.6035.1735
 */
class Treatment_Smtp_Authentication extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'smtp-authentication';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SMTP Authentication';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates SMTP authentication configuration and credentials';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the SMTP authentication treatment check.
	 *
	 * @since  1.6035.1735
	 * @return array|null Finding array if authentication issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SMTP_Authentication' );
	}

	/**
	 * Get SMTP authentication configuration from various sources.
	 *
	 * @since  1.6035.1735
	 * @return array|null SMTP authentication config array or null if not configured.
	 */
	private static function get_smtp_authentication_config() {
		// Check wp-config.php constants.
		if ( defined( 'WPMS_ON' ) && WPMS_ON ) {
			return array(
				'auth_enabled' => defined( 'WPMS_SMTP_AUTH' ) ? WPMS_SMTP_AUTH : true,
				'username'     => defined( 'WPMS_SMTP_USER' ) ? WPMS_SMTP_USER : '',
				'password'     => defined( 'WPMS_SMTP_PASS' ) ? WPMS_SMTP_PASS : '',
				'auth_type'    => defined( 'WPMS_SMTP_AUTOTLS' ) ? 'TLS' : 'default',
				'source'       => 'wp-config.php',
			);
		}

		// Check WP Mail SMTP plugin.
		if ( is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) ) {
			$options = get_option( 'wp_mail_smtp', array() );
			if ( ! empty( $options['mail'] ) ) {
				$mail = $options['mail'];
				return array(
					'auth_enabled' => ! empty( $mail['smtp_auth'] ),
					'username'     => $mail['smtp_user'] ?? '',
					'password'     => $mail['smtp_pass'] ?? '',
					'auth_type'    => $mail['smtp_auth_type'] ?? 'default',
					'source'       => 'WP Mail SMTP plugin',
				);
			}
		}

		// Check Easy WP SMTP plugin.
		if ( is_plugin_active( 'easy-wp-smtp/easy-wp-smtp.php' ) ) {
			$options = get_option( 'easy_wp_smtp', array() );
			if ( ! empty( $options['smtp_settings'] ) ) {
				$smtp = $options['smtp_settings'];
				return array(
					'auth_enabled' => ! empty( $smtp['autentication'] ), // Note: plugin has typo in option name.
					'username'     => $smtp['username'] ?? '',
					'password'     => $smtp['password'] ?? '',
					'auth_type'    => 'default',
					'source'       => 'Easy WP SMTP plugin',
				);
			}
		}

		// Check Post SMTP plugin.
		if ( is_plugin_active( 'post-smtp/postman-smtp.php' ) ) {
			$options = get_option( 'postman_options', array() );
			if ( ! empty( $options ) ) {
				return array(
					'auth_enabled' => ! empty( $options['auth_type'] ) && 'none' !== $options['auth_type'],
					'username'     => $options['basic_auth_username'] ?? '',
					'password'     => $options['basic_auth_password'] ?? '',
					'auth_type'    => $options['auth_type'] ?? 'default',
					'source'       => 'Post SMTP plugin',
				);
			}
		}

		return null;
	}
}
