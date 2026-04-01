<?php
/**
 * SMTP Authentication Diagnostic
 *
 * Tests SMTP authentication configuration and validates credentials.
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
 * SMTP Authentication Diagnostic Class
 *
 * Verifies that SMTP authentication is properly configured. This is like
 * having a password to prove who you are - without it, email servers will
 * reject your messages.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Smtp_Authentication extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'smtp-authentication';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SMTP Authentication';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates SMTP authentication configuration and credentials';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the SMTP authentication diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if authentication issues detected, null otherwise.
	 */
	public static function check() {
		$smtp_config = self::get_smtp_authentication_config();

		if ( ! $smtp_config ) {
			// No SMTP configured - different diagnostic handles this.
			return null;
		}

		$issues = array();

		// Check if authentication is enabled.
		if ( ! $smtp_config['auth_enabled'] ) {
			$issues[] = __( 'SMTP authentication is disabled - most email servers require authentication', 'wpshadow' );
		}

		// Check if username/password are configured.
		if ( $smtp_config['auth_enabled'] ) {
			if ( empty( $smtp_config['username'] ) ) {
				$issues[] = __( 'SMTP username is not configured', 'wpshadow' );
			}

			if ( empty( $smtp_config['password'] ) ) {
				$issues[] = __( 'SMTP password is not configured', 'wpshadow' );
			}
		}

		// Check authentication method compatibility.
		if ( $smtp_config['auth_enabled'] && ! empty( $smtp_config['auth_type'] ) ) {
			$supported_types = array( 'PLAIN', 'LOGIN', 'CRAM-MD5', 'XOAUTH2' );
			if ( ! in_array( strtoupper( $smtp_config['auth_type'] ), $supported_types, true ) ) {
				$issues[] = sprintf(
					/* translators: 1: configured auth type, 2: supported types */
					__( 'Authentication type "%1$s" may not be widely supported. Common types: %2$s', 'wpshadow' ),
					$smtp_config['auth_type'],
					implode( ', ', $supported_types )
				);
			}
		}

		// Check if test email authentication has failed recently.
		$last_auth_test = get_transient( 'wpshadow_smtp_auth_test_result' );
		if ( false !== $last_auth_test && 'auth_failed' === $last_auth_test ) {
			$issues[] = __( 'Last SMTP authentication test failed - credentials may be incorrect', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your email server authentication isn\'t properly set up (like trying to log into your email without the right password). Email servers need authentication to verify you\'re authorized to send emails. Without proper credentials, your emails will be rejected. This usually happens when username/password are missing or incorrect.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/smtp-authentication?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'context'      => array(
				'auth_enabled'  => $smtp_config['auth_enabled'],
				'username'      => $smtp_config['username'] ? '***' . substr( $smtp_config['username'], -3 ) : '',
				'password_set'  => ! empty( $smtp_config['password'] ),
				'auth_type'     => $smtp_config['auth_type'] ?? 'default',
				'source'        => $smtp_config['source'],
				'issues'        => $issues,
			),
		);
	}

	/**
	 * Get SMTP authentication configuration from various sources.
	 *
	 * @since 0.6093.1200
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
