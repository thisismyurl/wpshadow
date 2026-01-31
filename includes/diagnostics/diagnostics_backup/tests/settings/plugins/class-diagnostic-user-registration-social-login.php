<?php
/**
 * User Registration Social Login Diagnostic
 *
 * User Registration Social Login issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1228.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Registration Social Login Diagnostic Class
 *
 * @since 1.1228.0000
 */
class Diagnostic_UserRegistrationSocialLogin extends Diagnostic_Base {

	protected static $slug = 'user-registration-social-login';
	protected static $title = 'User Registration Social Login';
	protected static $description = 'User Registration Social Login issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check if User Registration plugin is active
		if ( ! class_exists( 'UserRegistration' ) && ! defined( 'UR_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check SSL
		if ( ! is_ssl() ) {
			$issues[] = 'ssl_not_enabled';
			$threat_level += 30;
		}

		// Get social login settings
		$ur_settings = get_option( 'user_registration_social_login_settings', array() );

		// Check Facebook login
		$fb_enabled = isset( $ur_settings['facebook_login'] ) ? $ur_settings['facebook_login'] : false;
		if ( $fb_enabled ) {
			$fb_app_id = isset( $ur_settings['facebook_app_id'] ) ? $ur_settings['facebook_app_id'] : '';
			$fb_secret = isset( $ur_settings['facebook_app_secret'] ) ? $ur_settings['facebook_app_secret'] : '';
			if ( empty( $fb_app_id ) || empty( $fb_secret ) ) {
				$issues[] = 'facebook_credentials_missing';
				$threat_level += 25;
			}
		}

		// Check Google login
		$google_enabled = isset( $ur_settings['google_login'] ) ? $ur_settings['google_login'] : false;
		if ( $google_enabled ) {
			$google_client_id = isset( $ur_settings['google_client_id'] ) ? $ur_settings['google_client_id'] : '';
			$google_secret = isset( $ur_settings['google_client_secret'] ) ? $ur_settings['google_client_secret'] : '';
			if ( empty( $google_client_id ) || empty( $google_secret ) ) {
				$issues[] = 'google_credentials_missing';
				$threat_level += 25;
			}
		}

		// Check rate limiting
		$rate_limit = isset( $ur_settings['social_login_rate_limit'] ) ? $ur_settings['social_login_rate_limit'] : false;
		if ( ! $rate_limit ) {
			$issues[] = 'rate_limiting_disabled';
			$threat_level += 15;
		}

		// Check user sync
		$sync_profile = isset( $ur_settings['sync_social_profile'] ) ? $ur_settings['sync_social_profile'] : false;
		if ( ! $sync_profile ) {
			$issues[] = 'profile_sync_disabled';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of social login issues */
				__( 'User Registration social login has issues: %s. This exposes OAuth credentials and creates security vulnerabilities.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/user-registration-social-login',
			);
		}
		
		return null;
	}
}
