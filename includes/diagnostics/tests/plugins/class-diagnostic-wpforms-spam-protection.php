<?php
/**
 * WPForms Spam Protection Diagnostic
 *
 * WPForms anti-spam settings not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.250.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPForms Spam Protection Diagnostic Class
 *
 * @since 1.250.0000
 */
class Diagnostic_WpformsSpamProtection extends Diagnostic_Base {

	protected static $slug = 'wpforms-spam-protection';
	protected static $title = 'WPForms Spam Protection';
	protected static $description = 'WPForms anti-spam settings not configured';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpforms' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CAPTCHA enabled
		$captcha = get_option( 'wpforms_captcha_enabled', 0 );
		if ( ! $captcha ) {
			$issues[] = 'CAPTCHA not enabled';
		}
		
		// Check 2: reCAPTCHA configuration
		$recaptcha = get_option( 'wpforms_recaptcha_configured', 0 );
		if ( ! $recaptcha ) {
			$issues[] = 'reCAPTCHA not properly configured';
		}
		
		// Check 3: Honeypot field
		$honeypot = get_option( 'wpforms_honeypot_enabled', 0 );
		if ( ! $honeypot ) {
			$issues[] = 'Honeypot field not enabled';
		}
		
		// Check 4: Rate limiting
		$rate = get_option( 'wpforms_rate_limiting_enabled', 0 );
		if ( ! $rate ) {
			$issues[] = 'Form submission rate limiting not enabled';
		}
		
		// Check 5: Spam filtering
		$filter = get_option( 'wpforms_spam_filtering_enabled', 0 );
		if ( ! $filter ) {
			$issues[] = 'Spam content filtering not enabled';
		}
		
		// Check 6: IP blocking
		$ip_block = get_option( 'wpforms_ip_blocking_enabled', 0 );
		if ( ! $ip_block ) {
			$issues[] = 'IP-based spam blocking not configured';
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
					'Found %d spam protection issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpforms-spam-protection',
			);
		}
		
		return null;
	}
}
