<?php
/**
 * User Registration Email Verification Diagnostic
 *
 * User Registration Email Verification issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1227.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Registration Email Verification Diagnostic Class
 *
 * @since 1.1227.0000
 */
class Diagnostic_UserRegistrationEmailVerification extends Diagnostic_Base {

	protected static $slug = 'user-registration-email-verification';
	protected static $title = 'User Registration Email Verification';
	protected static $description = 'User Registration Email Verification issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'UR_VERSION' ) && ! class_exists( 'UR_Form_Handler' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify email verification is enabled
		$email_verification = get_option( 'user_registration_enable_email_verification', 0 );
		if ( ! $email_verification ) {
			$issues[] = 'Email verification not enabled';
		}
		
		// Check 2: Check for verification email template
		$verification_template = get_option( 'user_registration_email_verification_email', '' );
		if ( $email_verification && empty( $verification_template ) ) {
			$issues[] = 'Verification email template not configured';
		}
		
		// Check 3: Verify account activation settings
		$require_activation = get_option( 'user_registration_admin_approval', 0 );
		if ( ! $require_activation ) {
			$issues[] = 'Account activation not required (spam risk)';
		}
		
		// Check 4: Check for resend verification limit
		$resend_limit = get_option( 'user_registration_verification_resend_limit', 0 );
		if ( $resend_limit <= 0 ) {
			$issues[] = 'Verification resend limit not configured';
		}
		
		// Check 5: Verify captcha on registration
		$captcha_enabled = get_option( 'user_registration_recaptcha', 0 );
		if ( ! $captcha_enabled ) {
			$issues[] = 'reCAPTCHA not enabled on registration form';
		}
		
		// Check 6: Check for email domain restrictions
		$allowed_domains = get_option( 'user_registration_allowed_email_domains', '' );
		if ( empty( $allowed_domains ) ) {
			$issues[] = 'Allowed email domains not restricted';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d user registration email verification issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/user-registration-email-verification',
			);
		}
		
		return null;
	}
}
