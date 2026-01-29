<?php
/**
 * Profile Builder Registration Security Diagnostic
 *
 * Profile Builder Registration Security issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1224.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Profile Builder Registration Security Diagnostic Class
 *
 * @since 1.1224.0000
 */
class Diagnostic_ProfileBuilderRegistrationSecurity extends Diagnostic_Base {

	protected static $slug = 'profile-builder-registration-security';
	protected static $title = 'Profile Builder Registration Security';
	protected static $description = 'Profile Builder Registration Security issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'PROFILE_BUILDER' ) && ! function_exists( 'wppb_init' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify reCAPTCHA is enabled
		$recaptcha_enabled = get_option( 'wppb_recaptcha_enabled', 'no' );
		if ( 'no' === $recaptcha_enabled ) {
			$issues[] = 'recaptcha_disabled';
		}
		
		// Check 2: Verify admin approval is required for new registrations
		$admin_approval = get_option( 'wppb_admin_approval', 'no' );
		if ( 'no' === $admin_approval ) {
			$issues[] = 'no_admin_approval_required';
		}
		
		// Check 3: Verify email confirmation is required
		$email_confirm = get_option( 'wppb_email_confirmation', 'no' );
		if ( 'no' === $email_confirm ) {
			$issues[] = 'no_email_confirmation';
		}
		
		// Check 4: Verify default user role is not administrator
		$default_role = get_option( 'wppb_default_role', 'subscriber' );
		if ( 'administrator' === $default_role || 'editor' === $default_role ) {
			$issues[] = 'elevated_default_role';
		}
		
		// Check 5: Check for spam registrations
		$recent_users = get_users( array(
			'date_query' => array(
				array(
					'after' => '24 hours ago',
				),
			),
			'number' => 100,
		) );
		
		if ( count( $recent_users ) > 50 ) {
			$issues[] = 'suspicious_registration_rate';
		}
		
		// Check 6: Verify minimum password strength is configured
		$min_password_strength = get_option( 'wppb_minimum_password_strength', '' );
		if ( empty( $min_password_strength ) || 'weak' === $min_password_strength ) {
			$issues[] = 'weak_password_allowed';
		}
		
		// Check 7: Verify username sanitization is enabled
		$sanitize_username = get_option( 'wppb_sanitize_usernames', 'yes' );
		if ( 'no' === $sanitize_username ) {
			$issues[] = 'username_sanitization_disabled';
		}
		
		// Check 8: Verify banned email domains are configured
		$banned_domains = get_option( 'wppb_banned_email_domains', '' );
		if ( empty( $banned_domains ) ) {
			$issues[] = 'no_banned_email_domains';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of registration security issues */
				__( 'Profile Builder registration has security issues: %s. Weak registration security can allow spam accounts, bot registrations, and unauthorized access.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/profile-builder-registration-security',
			);
		}
		
		return null;
	}
}
