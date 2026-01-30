<?php
/**
 * Elementor Form Spam Protection and Security Diagnostic
 *
 * Ensure Elementor forms have spam protection and security measures.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6030.1235
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Form Spam Protection Diagnostic Class
 *
 * @since 1.6030.1235
 */
class Diagnostic_ElementorFormSpamProtection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'elementor-form-spam-protection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Elementor Form Spam Protection and Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure Elementor forms have spam protection and security measures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.1235
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Elementor Pro is active (forms require Pro)
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			return null;
		}

		$issues = array();
		global $wpdb;

		// Check 1: Check reCAPTCHA configured on forms
		$recaptcha_settings = get_option( 'elementor_pro_recaptcha_site_key', '' );
		$recaptcha_secret = get_option( 'elementor_pro_recaptcha_secret_key', '' );
		
		$has_recaptcha = ! empty( $recaptcha_settings ) && ! empty( $recaptcha_secret );
		
		// Count forms in Elementor
		$form_count = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND meta_value LIKE '%\"widgetType\":\"form\"%'"
		);
		
		if ( ! $has_recaptcha && $form_count > 0 ) {
			$issues[] = 'reCAPTCHA not configured for forms';
		}

		// Check 2: Verify honeypot enabled
		$forms_with_honeypot = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND meta_value LIKE '%\"honeypot\":\"true\"%'"
		);
		
		if ( $form_count > 0 && $forms_with_honeypot < $form_count ) {
			$forms_without_honeypot = $form_count - $forms_with_honeypot;
			$issues[] = sprintf( '%d forms without honeypot protection', $forms_without_honeypot );
		}

		// Check 3: Test for form submission rate limiting
		$rate_limit_enabled = get_option( 'elementor_pro_forms_rate_limit', false );
		
		if ( ! $rate_limit_enabled && $form_count > 0 ) {
			$issues[] = 'form submission rate limiting not configured';
		}

		// Check 4: Check for email validation
		$email_fields = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND meta_value LIKE '%\"field_type\":\"email\"%'"
		);
		
		$validated_email_fields = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND meta_value LIKE '%\"field_type\":\"email\"%' 
			AND meta_value LIKE '%\"required\":true%'"
		);
		
		if ( $email_fields > 0 && $validated_email_fields < $email_fields ) {
			$issues[] = sprintf( '%d email fields not required', $email_fields - $validated_email_fields );
		}

		// Check 5: Test for GDPR consent checkboxes
		$consent_checkboxes = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND meta_value LIKE '%\"field_type\":\"acceptance\"%'"
		);
		
		if ( $form_count > 0 && $consent_checkboxes < $form_count ) {
			$issues[] = sprintf( '%d forms missing GDPR consent checkboxes', $form_count - $consent_checkboxes );
		}

		// Check 6: Verify sanitization of form fields (check for custom actions)
		$custom_actions = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND meta_value LIKE '%\"submit_actions\":%' 
			AND meta_value LIKE '%\"webhook\"%'"
		);
		
		if ( $custom_actions > 0 ) {
			$issues[] = sprintf( '%d forms with custom webhook actions (verify sanitization)', $custom_actions );
		}

		// Return finding if issues exist
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Elementor form security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-form-spam-protection',
			);
		}

		return null;
	}
}
