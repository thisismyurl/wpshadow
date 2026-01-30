<?php
/**
 * Gravity Forms Spam Protection Diagnostic
 *
 * Gravity Forms spam filtering inadequate.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.255.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Spam Protection Diagnostic Class
 *
 * @since 1.255.0000
 */
class Diagnostic_GravityFormsSpamProtection extends Diagnostic_Base {

	protected static $slug = 'gravity-forms-spam-protection';
	protected static $title = 'Gravity Forms Spam Protection';
	protected static $description = 'Gravity Forms spam filtering inadequate';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) {
			return null;
		}

		$issues = array();

		// Get all forms.
		$forms = \GFAPI::get_forms();

		// Check 1: Honeypot field enabled (built-in).
		$forms_without_honeypot = 0;
		foreach ( $forms as $form ) {
			if ( ! isset( $form['enableHoneypot'] ) || ! $form['enableHoneypot'] ) {
				$forms_without_honeypot++;
			}
		}
		if ( $forms_without_honeypot > 0 ) {
			$issues[] = "{$forms_without_honeypot} forms without honeypot protection";
		}

		// Check 2: Verify reCAPTCHA configured (v2/v3).
		$recaptcha_settings = get_option( 'rg_gforms_captcha_public_key' );
		$has_recaptcha = ! empty( $recaptcha_settings );
		
		if ( ! $has_recaptcha ) {
			$issues[] = 'reCAPTCHA not configured globally';
			
			// Check individual forms for reCAPTCHA.
			$forms_with_captcha = 0;
			foreach ( $forms as $form ) {
				if ( ! empty( $form['fields'] ) ) {
					foreach ( $form['fields'] as $field ) {
						if ( 'captcha' === $field->type ) {
							$forms_with_captcha++;
							break;
						}
					}
				}
			}
			if ( $forms_with_captcha === 0 && count( $forms ) > 0 ) {
				$issues[] = 'no forms have CAPTCHA protection';
			}
		}

		// Check 3: Test for Akismet integration (if available).
		if ( is_plugin_active( 'akismet/akismet.php' ) ) {
			$forms_without_akismet = 0;
			foreach ( $forms as $form ) {
				if ( ! isset( $form['enableAkismet'] ) || ! $form['enableAkismet'] ) {
					$forms_without_akismet++;
				}
			}
			if ( $forms_without_akismet > 0 ) {
				$issues[] = "{$forms_without_akismet} forms not using Akismet (available but not enabled)";
			}
		}

		// Check 4: Test submission rate limiting.
		global $wpdb;
		$recent_submissions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}gf_entry WHERE date_created > DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
			)
		);
		if ( $recent_submissions > 50 ) {
			$issues[] = "{$recent_submissions} submissions in last 5 minutes (possible spam flood)";
		}

		// Check 5: Check for IP blacklisting capability.
		$has_spam_protection_addon = is_plugin_active( 'gravityforms-anti-spam/gravityforms-anti-spam.php' );
		if ( ! $has_spam_protection_addon && count( $forms ) > 0 ) {
			$issues[] = 'no advanced spam protection addon installed';
		}

		// Check 6: Verify notification email not compromised.
		$forms_with_suspicious_notifications = 0;
		foreach ( $forms as $form ) {
			if ( ! empty( $form['notifications'] ) ) {
				foreach ( $form['notifications'] as $notification ) {
					$to_email = isset( $notification['to'] ) ? $notification['to'] : '';
					// Check for suspicious patterns in email addresses.
					if ( preg_match( '/@(tempmail|throwaway|guerrillamail|mailinator)/i', $to_email ) ) {
						$forms_with_suspicious_notifications++;
						break;
					}
				}
			}
		}
		if ( $forms_with_suspicious_notifications > 0 ) {
			$issues[] = "{$forms_with_suspicious_notifications} forms with suspicious notification emails";
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 75 + ( count( $issues ) * 3 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Spam protection issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gravity-forms-spam-protection',
			);
		}

		return null;
	}
}
