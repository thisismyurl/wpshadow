<?php
/**
 * Wp User Frontend Registration Spam Diagnostic
 *
 * Wp User Frontend Registration Spam issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1221.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp User Frontend Registration Spam Diagnostic Class
 *
 * @since 1.1221.0000
 */
class Diagnostic_WpUserFrontendRegistrationSpam extends Diagnostic_Base {

	protected static $slug = 'wp-user-frontend-registration-spam';
	protected static $title = 'Wp User Frontend Registration Spam';
	protected static $description = 'Wp User Frontend Registration Spam issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WPUF_Frontend_Form_Profile' ) && ! function_exists( 'wpuf_get_option' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify CAPTCHA is enabled on registration forms
		$captcha_enabled = get_option( 'wpuf_enable_captcha', 0 );
		if ( ! $captcha_enabled ) {
			$issues[] = 'CAPTCHA not enabled on registration forms';
		}
		
		// Check 2: Check for reCAPTCHA configuration
		if ( $captcha_enabled ) {
			$site_key = get_option( 'wpuf_recaptcha_site_key', '' );
			$secret_key = get_option( 'wpuf_recaptcha_secret_key', '' );
			if ( empty( $site_key ) || empty( $secret_key ) ) {
				$issues[] = 'reCAPTCHA keys not configured';
			}
		}
		
		// Check 3: Verify email confirmation is required
		$require_email_confirm = get_option( 'wpuf_require_email_confirmation', 0 );
		if ( ! $require_email_confirm ) {
			$issues[] = 'Email confirmation not required for new registrations';
		}
		
		// Check 4: Check for admin approval
		$require_approval = get_option( 'wpuf_require_admin_approval', 0 );
		if ( ! $require_approval ) {
			$issues[] = 'Admin approval not required for registrations';
		}
		
		// Check 5: Verify honeypot field
		$honeypot_enabled = get_option( 'wpuf_enable_honeypot', 0 );
		if ( ! $honeypot_enabled ) {
			$issues[] = 'Honeypot spam protection not enabled';
		}
		
		// Check 6: Check for registration rate limiting
		$rate_limit = get_option( 'wpuf_registration_rate_limit', 0 );
		if ( ! $rate_limit ) {
			$issues[] = 'Registration rate limiting not configured';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d WP User Frontend registration spam issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-user-frontend-registration-spam',
			);
		}
		
		return null;
	}
}
