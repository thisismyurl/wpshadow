<?php
/**
 * Post Smtp Mailer Oauth Security Diagnostic
 *
 * Post Smtp Mailer Oauth Security issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1460.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Smtp Mailer Oauth Security Diagnostic Class
 *
 * @since 1.1460.0000
 */
class Diagnostic_PostSmtpMailerOauthSecurity extends Diagnostic_Base {

	protected static $slug = 'post-smtp-mailer-oauth-security';
	protected static $title = 'Post Smtp Mailer Oauth Security';
	protected static $description = 'Post Smtp Mailer Oauth Security issue found';
	protected static $family = 'security';

	public static function check() {
		// Check if Post SMTP is installed
		if ( ! defined( 'POST_SMTP_VER' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Get Post SMTP options
		$options = get_option( 'postman_options', array() );
		if ( empty( $options ) ) {
			return null;
		}

		// Check authentication type
		$auth_type = isset( $options['auth_type'] ) ? $options['auth_type'] : '';
		if ( $auth_type === 'oauth2' ) {
			// Check OAuth configuration
			$client_id = isset( $options['oauth_client_id'] ) ? $options['oauth_client_id'] : '';
			$client_secret = isset( $options['oauth_client_secret'] ) ? $options['oauth_client_secret'] : '';

			if ( empty( $client_id ) || empty( $client_secret ) ) {
				$issues[] = 'oauth_credentials_missing';
				$threat_level += 25;
			}

			// Check if credentials are encrypted
			$encryption = isset( $options['enc_type'] ) ? $options['enc_type'] : 'none';
			if ( $encryption === 'none' ) {
				$issues[] = 'credentials_not_encrypted';
				$threat_level += 20;
			}

			// Check refresh token
			$refresh_token = isset( $options['oauth_refresh_token'] ) ? $options['oauth_refresh_token'] : '';
			if ( empty( $refresh_token ) ) {
				$issues[] = 'no_refresh_token';
				$threat_level += 15;
			}

			// Check token expiration
			$token_expires = isset( $options['oauth_token_expires'] ) ? $options['oauth_token_expires'] : 0;
			if ( $token_expires > 0 && $token_expires < time() ) {
				$issues[] = 'oauth_token_expired';
				$threat_level += 20;
			}
		}

		// Check for plain password storage
		$password = isset( $options['basic_auth_password'] ) ? $options['basic_auth_password'] : '';
		if ( ! empty( $password ) && $auth_type === 'login' ) {
			$issues[] = 'plain_password_auth';
			$threat_level += 25;
		}

		// Check SSL/TLS configuration
		$transport_type = isset( $options['transport_type'] ) ? $options['transport_type'] : '';
		$security = isset( $options['enc_type'] ) ? $options['enc_type'] : 'none';
		if ( $security === 'none' && $transport_type === 'smtp' ) {
			$issues[] = 'smtp_not_encrypted';
			$threat_level += 20;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of OAuth security issues */
				__( 'Post SMTP OAuth security has vulnerabilities: %s. This can expose email credentials and allow unauthorized email sending.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-smtp-mailer-oauth-security',
			);
		}
		
		return null;
	}
}
