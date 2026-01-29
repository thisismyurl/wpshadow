<?php
/**
 * BuddyPress Registration Security Diagnostic
 *
 * BuddyPress user registration not properly secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.234.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Registration Security Diagnostic Class
 *
 * @since 1.234.0000
 */
class Diagnostic_BuddypressRegistrationSecurity extends Diagnostic_Base {

	protected static $slug = 'buddypress-registration-security';
	protected static $title = 'BuddyPress Registration Security';
	protected static $description = 'BuddyPress user registration not properly secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'buddypress' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check if registration is enabled
		$registration_enabled = get_option( 'users_can_register', 0 );
		if ( ! $registration_enabled ) {
			return null; // Registration disabled, no security concerns
		}

		// Check for CAPTCHA protection
		$recaptcha_site_key = get_option( 'bp_recaptcha_site_key', '' );
		$recaptcha_secret = get_option( 'bp_recaptcha_secret_key', '' );
		if ( empty( $recaptcha_site_key ) || empty( $recaptcha_secret ) ) {
			$issues[] = 'no_captcha_protection';
			$threat_level += 25;
		}

		// Check email activation requirement
		$require_activation = get_option( 'bp_require_membership_requests', false );
		if ( ! $require_activation ) {
			$issues[] = 'no_email_activation';
			$threat_level += 15;
		}

		// Check for required profile fields
		$profile_fields = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}bp_xprofile_fields 
				 WHERE is_required = %d",
				1
			)
		);
		if ( $profile_fields < 2 ) {
			$issues[] = 'insufficient_required_fields';
			$threat_level += 10;
		}

		// Check for username/email restrictions
		$illegal_names = get_site_option( 'illegal_names', array() );
		if ( empty( $illegal_names ) || count( $illegal_names ) < 5 ) {
			$issues[] = 'no_username_blacklist';
			$threat_level += 10;
		}

		// Check for recent registration spam
		$recent_signups = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}signups 
				 WHERE registered > %s AND active = %d",
				date( 'Y-m-d H:i:s', strtotime( '-24 hours' ) ),
				0
			)
		);
		if ( $recent_signups > 50 ) {
			$issues[] = 'excessive_pending_registrations';
			$threat_level += 15;
		}

		// Check admin moderation
		$moderate_signups = get_option( 'bp_moderate_signups', 'none' );
		if ( $moderate_signups === 'none' ) {
			$issues[] = 'no_admin_moderation';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of registration security issues */
				__( 'BuddyPress registration security has weaknesses: %s. This can allow spam accounts, fake profiles, and abuse of your community platform.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-registration-security',
			);
		}
		
		return null;
	}
}
