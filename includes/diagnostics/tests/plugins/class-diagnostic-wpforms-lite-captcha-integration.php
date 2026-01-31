<?php
/**
 * Wpforms Lite Captcha Integration Diagnostic
 *
 * Wpforms Lite Captcha Integration issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1199.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpforms Lite Captcha Integration Diagnostic Class
 *
 * @since 1.1199.0000
 */
class Diagnostic_WpformsLiteCaptchaIntegration extends Diagnostic_Base {

	protected static $slug = 'wpforms-lite-captcha-integration';
	protected static $title = 'Wpforms Lite Captcha Integration';
	protected static $description = 'Wpforms Lite Captcha Integration issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'wpforms' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify CAPTCHA is enabled
		$captcha_provider = get_option( 'wpforms_settings', array() );
		$captcha_enabled = isset( $captcha_provider['recaptcha-type'] ) ? $captcha_provider['recaptcha-type'] : '';
		if ( empty( $captcha_enabled ) ) {
			$issues[] = 'CAPTCHA not enabled on forms';
		}

		// Check 2: Check for valid reCAPTCHA keys
		if ( ! empty( $captcha_enabled ) ) {
			$site_key = isset( $captcha_provider['recaptcha-site-key'] ) ? $captcha_provider['recaptcha-site-key'] : '';
			$secret_key = isset( $captcha_provider['recaptcha-secret-key'] ) ? $captcha_provider['recaptcha-secret-key'] : '';
			if ( empty( $site_key ) || empty( $secret_key ) ) {
				$issues[] = 'reCAPTCHA keys not configured';
			}
		}

		// Check 3: Verify CAPTCHA version (v2 vs v3)
		if ( $captcha_enabled === 'v2' ) {
			$issues[] = 'Using reCAPTCHA v2 (v3 is more user-friendly)';
		}

		// Check 4: Check for CAPTCHA on all public forms
		$forms = wpforms()->form->get( '', array( 'orderby' => 'ID' ) );
		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$form_data = wpforms_decode( $form->post_content );
				if ( ! isset( $form_data['settings']['recaptcha'] ) || $form_data['settings']['recaptcha'] != '1' ) {
					$issues[] = sprintf( 'Form "%s" does not have CAPTCHA enabled', $form->post_title );
					break;
				}
			}
		}

		// Check 5: Verify CAPTCHA threshold (v3)
		if ( $captcha_enabled === 'v3' ) {
			$threshold = isset( $captcha_provider['recaptcha-v3-threshold'] ) ? (float) $captcha_provider['recaptcha-v3-threshold'] : 0.5;
			if ( $threshold < 0.5 ) {
				$issues[] = 'reCAPTCHA v3 threshold too low (allows more spam)';
			}
		}

		// Check 6: Check for hCaptcha alternative
		$hcaptcha = isset( $captcha_provider['hcaptcha-site-key'] ) ? $captcha_provider['hcaptcha-site-key'] : '';
		if ( empty( $captcha_enabled ) && empty( $hcaptcha ) ) {
			$issues[] = 'No CAPTCHA provider configured (reCAPTCHA or hCaptcha)';
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
					'Found %d WPForms CAPTCHA integration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpforms-lite-captcha-integration',
			);
		}

		return null;
	}
}
