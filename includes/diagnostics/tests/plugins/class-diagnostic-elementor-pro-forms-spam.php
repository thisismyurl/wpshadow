<?php
/**
 * Elementor Pro Forms Spam Diagnostic
 *
 * Elementor Pro Forms Spam issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.788.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Forms Spam Diagnostic Class
 *
 * @since 1.788.0000
 */
class Diagnostic_ElementorProFormsSpam extends Diagnostic_Base {

	protected static $slug = 'elementor-pro-forms-spam';
	protected static $title = 'Elementor Pro Forms Spam';
	protected static $description = 'Elementor Pro Forms Spam issues found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify reCAPTCHA is enabled
		$recaptcha = get_option( 'elementor_pro_forms_recaptcha', 0 );
		if ( ! $recaptcha ) {
			$issues[] = 'reCAPTCHA not enabled for forms';
		}

		// Check 2: Check for honeypot
		$honeypot = get_option( 'elementor_pro_forms_honeypot', 0 );
		if ( ! $honeypot ) {
			$issues[] = 'Honeypot protection not enabled';
		}

		// Check 3: Verify Akismet integration
		$akismet = get_option( 'elementor_pro_forms_akismet', 0 );
		if ( ! $akismet ) {
			$issues[] = 'Akismet integration not enabled';
		}

		// Check 4: Check for rate limiting
		$rate_limit = get_option( 'elementor_pro_forms_rate_limit', 0 );
		if ( ! $rate_limit ) {
			$issues[] = 'Form submission rate limiting not enabled';
		}

		// Check 5: Verify email validation
		$email_validation = get_option( 'elementor_pro_forms_email_validation', 0 );
		if ( ! $email_validation ) {
			$issues[] = 'Email validation not enabled';
		}

		// Check 6: Check for IP blocking
		$ip_blocking = get_option( 'elementor_pro_forms_ip_blocking', 0 );
		if ( ! $ip_blocking ) {
			$issues[] = 'IP blocking not enabled for repeat spam';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Elementor Pro forms spam issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-pro-forms-spam',
			);
		}

		return null;
	}
}
