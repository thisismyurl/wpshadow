<?php
/**
 * Constant Contact Form Validation Diagnostic
 *
 * Constant Contact Form Validation configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.723.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Constant Contact Form Validation Diagnostic Class
 *
 * @since 1.723.0000
 */
class Diagnostic_ConstantContactFormValidation extends Diagnostic_Base {

	protected static $slug = 'constant-contact-form-validation';
	protected static $title = 'Constant Contact Form Validation';
	protected static $description = 'Constant Contact Form Validation configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		$has_cc = defined( 'CONSTANT_CONTACT_VERSION' ) ||
		          class_exists( 'ConstantContact' ) ||
		          function_exists( 'constant_contact' );

		if ( ! $has_cc ) {
			return null;
		}

		$issues = array();

		// Check 1: API credentials
		$api_key = get_option( 'constant_contact_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'No API key (forms not connected)', 'wpshadow' );
		}

		// Check 2: Email validation
		$validate_email = get_option( 'constant_contact_validate_email', 'no' );
		if ( 'no' === $validate_email ) {
			$issues[] = __( 'Email validation disabled (bad data)', 'wpshadow' );
		}

		// Check 3: Double opt-in
		$double_optin = get_option( 'constant_contact_double_optin', 'no' );
		if ( 'no' === $double_optin ) {
			$issues[] = __( 'Single opt-in (spam complaints)', 'wpshadow' );
		}

		// Check 4: reCAPTCHA
		$recaptcha = get_option( 'constant_contact_recaptcha', 'no' );
		if ( 'no' === $recaptcha ) {
			$issues[] = __( 'No CAPTCHA (bot submissions)', 'wpshadow' );
		}

		// Check 5: Error logging
		$log_errors = get_option( 'constant_contact_log_errors', 'no' );
		if ( 'no' === $log_errors ) {
			$issues[] = __( 'Errors not logged (troubleshooting)', 'wpshadow' );
		}

		// Check 6: Required fields
		$require_name = get_option( 'constant_contact_require_name', 'no' );
		if ( 'no' === $require_name ) {
			$issues[] = __( 'Name not required (incomplete data)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Constant Contact forms have %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/constant-contact-form-validation',
		);
	}
}
