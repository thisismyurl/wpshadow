<?php
/**
 * SMTP Authentication Diagnostic
 *
 * Validates SMTP authentication credentials are properly configured.
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
 * SMTP Authentication Diagnostic Class
 *
 * Checks if SMTP authentication is properly configured with valid credentials.
 *
 * @since 1.6093.1200
 */
class Diagnostic_SMTP_Authentication extends Diagnostic_Base {

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
	protected static $description = 'Verifies SMTP authentication credentials are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-deliverability';

	/**
	 * Run the SMTP authentication diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if authentication issue detected, null otherwise.
	 */
	public static function check() {
		$smtp_config = self::get_smtp_authentication();

		if ( empty( $smtp_config['configured'] ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'SMTP authentication is not configured. This may cause email delivery failures.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/smtp-authentication',
			);
		}

		if ( empty( $smtp_config['username'] ) || empty( $smtp_config['password'] ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'SMTP authentication is configured but missing credentials (username or password).', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/smtp-authentication',
			);
		}

		return null;
	}

	/**
	 * Get SMTP authentication configuration from various sources.
	 *
	 * @since 1.6093.1200
	 * @return array SMTP authentication configuration.
	 */
	private static function get_smtp_authentication(): array {
		$config = array(
			'configured' => false,
			'username'   => '',
			'password'   => '',
			'auth_type'  => '',
		);

		// Check wp-config.php constants.
		if ( defined( 'WPMS_ON' ) && WPMS_ON ) {
			if ( defined( 'WPMS_SMTP_USER' ) && defined( 'WPMS_SMTP_PASS' ) ) {
				$config['configured'] = true;
				$config['username']   = WPMS_SMTP_USER;
				$config['password']   = '***'; // Don't expose password.
				return $config;
			}
		}

		// Check WP Mail SMTP plugin.
		$wp_mail_smtp = get_option( 'wp_mail_smtp' );
		if ( ! empty( $wp_mail_smtp['mail']['mailer'] ) && 'smtp' === $wp_mail_smtp['mail']['mailer'] ) {
			if ( ! empty( $wp_mail_smtp['smtp']['user'] ) && ! empty( $wp_mail_smtp['smtp']['pass'] ) ) {
				$config['configured'] = true;
				$config['username']   = $wp_mail_smtp['smtp']['user'];
				$config['password']   = '***';
				if ( ! empty( $wp_mail_smtp['smtp']['auth'] ) ) {
					$config['auth_type'] = $wp_mail_smtp['smtp']['auth'];
				}
				return $config;
			}
		}

		// Check Easy WP SMTP plugin.
		$easy_wp_smtp = get_option( 'swpsmtp_options' );
		if ( ! empty( $easy_wp_smtp['smtp_settings']['username'] ) && ! empty( $easy_wp_smtp['smtp_settings']['password'] ) ) {
			$config['configured'] = true;
			$config['username']   = $easy_wp_smtp['smtp_settings']['username'];
			$config['password']   = '***';
			return $config;
		}

		// Check Post SMTP plugin.
		$post_smtp = get_option( 'postman_options' );
		if ( ! empty( $post_smtp['basic_auth_username'] ) && ! empty( $post_smtp['basic_auth_password'] ) ) {
			$config['configured'] = true;
			$config['username']   = $post_smtp['basic_auth_username'];
			$config['password']   = '***';
			return $config;
		}

		return $config;
	}
}
