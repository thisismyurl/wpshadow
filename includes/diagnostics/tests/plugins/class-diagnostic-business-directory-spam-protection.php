<?php
/**
 * Business Directory Spam Protection Diagnostic
 *
 * Business Directory spam not filtered.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.548.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business Directory Spam Protection Diagnostic Class
 *
 * @since 1.548.0000
 */
class Diagnostic_BusinessDirectorySpamProtection extends Diagnostic_Base {

	protected static $slug = 'business-directory-spam-protection';
	protected static $title = 'Business Directory Spam Protection';
	protected static $description = 'Business Directory spam not filtered';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: CAPTCHA enabled for submissions
		$captcha = get_option( 'wpbdp_settings_recaptcha_enabled', false );
		if ( ! $captcha ) {
			$issues[] = __( 'reCAPTCHA not enabled for directory submissions', 'wpshadow' );
		}
		
		// Check 2: Listing moderation queue
		$moderation = get_option( 'wpbdp_settings_new_post_status', 'publish' );
		if ( $moderation === 'publish' ) {
			$issues[] = __( 'Listings auto-publish without moderation', 'wpshadow' );
		}
		
		// Check 3: Submission rate limiting
		$rate_limit = get_option( 'wpbdp_settings_submission_rate_limit', 0 );
		if ( $rate_limit === 0 ) {
			$issues[] = __( 'No submission rate limiting configured', 'wpshadow' );
		}
		
		// Check 4: Check for spam listings
		$spam_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s",
				'wpbdp_listing',
				'spam'
			)
		);
		
		if ( $spam_count > 20 ) {
			$issues[] = sprintf( __( '%d spam listings detected (improve filtering)', 'wpshadow' ), $spam_count );
		}
		
		// Check 5: Email/domain blacklist
		$blacklist = get_option( 'wpbdp_settings_email_blacklist', array() );
		if ( empty( $blacklist ) ) {
			$issues[] = __( 'Email/domain blacklist not configured', 'wpshadow' );
		}
		
		// Check 6: Akismet integration
		$akismet = get_option( 'wpbdp_settings_akismet_enabled', false );
		if ( ! $akismet && function_exists( 'akismet_http_post' ) ) {
			$issues[] = __( 'Akismet available but not enabled for directory', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 60;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 75;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 68;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'Business Directory has %d spam protection issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/business-directory-spam-protection',
		);
	}
}
