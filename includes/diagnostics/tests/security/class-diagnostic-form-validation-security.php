<?php
/**
 * Form Validation Security Diagnostic
 *
 * Tests if forms have proper client-side and server-side validation security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1020
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Validation Security Diagnostic Class
 *
 * Validates that forms have proper security measures including nonce
 * validation, CSRF protection, and input sanitization.
 *
 * @since 1.7034.1020
 */
class Diagnostic_Form_Validation_Security extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-validation-security';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Validation Security';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if forms have proper validation and security measures';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if forms have nonce protection, CSRF prevention,
	 * input validation, and server-side sanitization.
	 *
	 * @since  1.7034.1020
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for form security plugins.
		$has_cf7 = is_plugin_active( 'contact-form-7/wp-contact-form-7.php' );
		$has_gravity = is_plugin_active( 'gravityforms/gravityforms.php' );
		$has_wpforms = is_plugin_active( 'wpforms-lite/wpforms.php' ) ||
					   is_plugin_active( 'wpforms/wpforms.php' );
		$has_formidable = is_plugin_active( 'formidable/formidable.php' );

		// Check if any forms are using AJAX.
		$has_ajax_forms = has_action( 'wp_ajax_wpcf7_submit' ) || 
						has_action( 'wp_ajax_gf_submit_form' );

		// Check if nonce function is registered.
		$nonce_check = function_exists( 'wp_verify_nonce' ) && 
					  function_exists( 'wp_create_nonce' );

		// Check for form-related sanitization functions.
		$sanitize_check = function_exists( 'sanitize_text_field' ) &&
						function_exists( 'sanitize_email' );

		// Check WordPress filters for form sanitization.
		$has_sanitize_filter = has_filter( 'wp_form_input_sanitize' );

		// Check registered form post types.
		$cf7_forms = 0;
		if ( $has_cf7 ) {
			$cf7_forms = wp_count_posts( 'wpcf7_contact_form' )->publish ?? 0;
		}

		// Test form security implementation.
		global $wpdb;
		$contact_forms = $wpdb->get_results(
			"SELECT p.ID, p.post_title, p.post_content
			 FROM {$wpdb->posts} p
			 WHERE p.post_type = 'wpcf7_contact_form'
			 LIMIT 5",
			ARRAY_A
		);

		$forms_with_security = 0;
		$forms_checked = 0;

		foreach ( $contact_forms as $form ) {
			$content = $form['post_content'] ?? '';
			$forms_checked++;

			// Simple check for nonce-like patterns.
			if ( strpos( $content, '_wpnonce' ) !== false || 
				 strpos( $content, 'nonce' ) !== false ) {
				$forms_with_security++;
			}
		}

		// Check form recaptcha protection.
		$has_recaptcha = is_plugin_active( 'wp-recaptcha-integration/wp-recaptcha.php' ) ||
						is_plugin_active( 'google-site-kit/google-site-kit.php' );

		// Check reCAPTCHA plugin option.
		$recaptcha_keys = get_option( 'recaptcha_site_key' );
		$has_recaptcha_configured = ! empty( $recaptcha_keys );

		// Check CORS headers on forms.
		$rest_enabled = ! ( defined( 'REST_API_DISABLED' ) && REST_API_DISABLED );

		// Check for issues.
		$issues = array();

		// Issue 1: No form plugins with built-in security.
		if ( ! $has_cf7 && ! $has_gravity && ! $has_wpforms && ! $has_formidable ) {
			$issues[] = array(
				'type'        => 'no_form_plugin',
				'description' => __( 'No established form plugin detected; custom forms may lack security measures', 'wpshadow' ),
			);
		}

		// Issue 2: AJAX forms without verification.
		if ( $has_ajax_forms && ! $nonce_check ) {
			$issues[] = array(
				'type'        => 'ajax_no_nonce',
				'description' => __( 'AJAX forms detected but nonce verification function not available', 'wpshadow' ),
			);
		}

		// Issue 3: Forms exist but security implementation unclear.
		if ( $forms_checked > 0 && $forms_with_security < ( $forms_checked * 0.5 ) ) {
			$issues[] = array(
				'type'        => 'weak_form_security',
				'description' => sprintf(
					/* translators: %d: percentage of forms with apparent security */
					__( 'Only %d%% of forms show clear security implementation', 'wpshadow' ),
					round( ( $forms_with_security / $forms_checked ) * 100 )
				),
			);
		}

		// Issue 4: No CAPTCHA protection on forms.
		if ( $cf7_forms > 0 && ! $has_recaptcha_configured ) {
			$issues[] = array(
				'type'        => 'no_captcha',
				'description' => __( 'Forms exist but no CAPTCHA/reCAPTCHA protection configured for spam prevention', 'wpshadow' ),
			);
		}

		// Issue 5: REST API enabled without form security check.
		if ( $rest_enabled && ! $has_cf7 ) {
			$issues[] = array(
				'type'        => 'rest_form_exposure',
				'description' => __( 'REST API enabled but no established form plugin; custom form endpoints may be vulnerable', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Forms may lack proper validation and security measures including nonce protection, CSRF prevention, and input sanitization', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/form-validation-security',
				'details'      => array(
					'has_cf7'                  => $has_cf7,
					'has_gravity_forms'        => $has_gravity,
					'has_wpforms'              => $has_wpforms,
					'has_formidable'           => $has_formidable,
					'has_ajax_forms'           => $has_ajax_forms,
					'nonce_functions_available' => $nonce_check,
					'sanitize_functions_available' => $sanitize_check,
					'cf7_forms_count'          => absint( $cf7_forms ),
					'forms_with_security'      => $forms_with_security,
					'forms_checked'            => $forms_checked,
					'has_recaptcha'            => $has_recaptcha,
					'recaptcha_configured'     => $has_recaptcha_configured,
					'rest_api_enabled'         => $rest_enabled,
					'issues_detected'          => $issues,
					'recommendation'           => __( 'Use established form plugins (CF7, Gravity Forms, WPForms) with nonce protection and CAPTCHA', 'wpshadow' ),
					'security_checklist'       => array(
						'nonce_validation'     => 'Verify form request is from authorized source',
						'input_sanitization'   => 'Clean user input before processing',
						'output_escaping'      => 'Escape output to prevent XSS',
						'capability_check'     => 'Verify user has permission to submit',
						'rate_limiting'        => 'Prevent spam and abuse',
						'captcha_verification' => 'Verify user is human',
					),
					'vulnerable_patterns'      => array(
						'Direct $_POST access without sanitization',
						'No nonce field in forms',
						'Missing capability checks',
						'Unescaped database output',
						'No CAPTCHA on public forms',
					),
				),
			);
		}

		return null;
	}
}
