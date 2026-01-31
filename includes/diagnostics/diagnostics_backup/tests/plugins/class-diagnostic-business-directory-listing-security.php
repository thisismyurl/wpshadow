<?php
/**
 * Business Directory Listing Security Diagnostic
 *
 * Business Directory listings not protected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.546.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business Directory Listing Security Diagnostic Class
 *
 * @since 1.546.0000
 */
class Diagnostic_BusinessDirectoryListingSecurity extends Diagnostic_Base {

	protected static $slug = 'business-directory-listing-security';
	protected static $title = 'Business Directory Listing Security';
	protected static $description = 'Business Directory listings not protected';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Listing moderation enabled
		$moderation = get_option( 'wpbdp_listing_moderation', false );
		if ( ! $moderation ) {
			$issues[] = 'Listing moderation disabled';
		}

		// Check 2: Spam protection enabled
		$spam_protection = get_option( 'wpbdp_spam_protection', false );
		if ( ! $spam_protection ) {
			$issues[] = 'Spam protection disabled';
		}

		// Check 3: User verification required
		$user_verification = get_option( 'wpbdp_user_verification', false );
		if ( ! $user_verification ) {
			$issues[] = 'User verification not required';
		}

		// Check 4: Secure listing submissions
		if ( ! is_ssl() ) {
			$issues[] = 'HTTPS not enabled for submissions';
		}

		// Check 5: reCAPTCHA enabled
		$recaptcha = get_option( 'wpbdp_recaptcha_enabled', false );
		if ( ! $recaptcha ) {
			$issues[] = 'reCAPTCHA not enabled';
		}

		// Check 6: Content sanitization enabled
		$content_sanitization = get_option( 'wpbdp_content_sanitization', false );
		if ( ! $content_sanitization ) {
			$issues[] = 'Content sanitization disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Business directory listing security issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/business-directory-listing-security',
			);
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
