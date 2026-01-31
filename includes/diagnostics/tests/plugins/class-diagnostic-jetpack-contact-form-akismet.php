<?php
/**
 * Jetpack Contact Form Akismet Diagnostic
 *
 * Jetpack Contact Form Akismet issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1220.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Contact Form Akismet Diagnostic Class
 *
 * @since 1.1220.0000
 */
class Diagnostic_JetpackContactFormAkismet extends Diagnostic_Base {

	protected static $slug = 'jetpack-contact-form-akismet';
	protected static $title = 'Jetpack Contact Form Akismet';
	protected static $description = 'Jetpack Contact Form Akismet issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Jetpack' ) || ! function_exists( 'jetpack_is_module_active' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify Akismet integration is enabled
		$akismet_enabled = get_option( 'jetpack_contact_form_akismet', false );
		if ( ! $akismet_enabled || ! jetpack_is_module_active( 'contact-form' ) ) {
			$issues[] = __( 'Akismet spam filtering not enabled for contact forms', 'wpshadow' );
		}

		// Check 2: Check Akismet API key validity
		$akismet_key = get_option( 'wordpress_api_key', '' );
		if ( empty( $akismet_key ) ) {
			$issues[] = __( 'Akismet API key not configured', 'wpshadow' );
		}

		// Check 3: Verify spam submission logging
		$log_spam = get_option( 'jetpack_contact_form_log_spam', false );
		if ( ! $log_spam ) {
			$issues[] = __( 'Spam submission logging not enabled', 'wpshadow' );
		}

		// Check 4: Check false positive handling
		$manual_review = get_option( 'jetpack_contact_form_manual_spam_review', false );
		if ( ! $manual_review ) {
			$issues[] = __( 'Manual spam review not enabled for false positives', 'wpshadow' );
		}

		// Check 5: Verify spam submission retention
		$spam_retention = get_option( 'jetpack_contact_form_spam_retention_days', 0 );
		if ( $spam_retention > 30 || $spam_retention === 0 ) {
			$issues[] = __( 'Spam submission retention period too long', 'wpshadow' );
		}

		// Check 6: Check blocked submission monitoring
		$monitor_blocked = get_option( 'jetpack_contact_form_monitor_blocked', false );
		if ( ! $monitor_blocked ) {
			$issues[] = __( 'Blocked submission monitoring not enabled', 'wpshadow' );
		}
		return null;
	}
}
