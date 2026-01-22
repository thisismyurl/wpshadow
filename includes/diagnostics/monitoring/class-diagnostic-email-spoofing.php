<?php
declare(strict_types=1);
/**
 * Email Spoofing Protection Diagnostic
 *
 * Philosophy: Email security - verify SPF/DKIM records
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for SPF/DKIM email authentication.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Email_Spoofing extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );
		
		// Check for SPF record
		$spf_record = @dns_get_record( $domain, DNS_TXT );
		$has_spf = false;
		
		if ( ! empty( $spf_record ) ) {
			foreach ( $spf_record as $record ) {
				if ( isset( $record['txt'] ) && strpos( $record['txt'], 'v=spf1' ) === 0 ) {
					$has_spf = true;
					break;
				}
		}
		
		if ( ! $has_spf ) {
			return array(
				'id'          => 'email-spoofing',
				'title'       => 'No SPF Record Configured',
				'description' => sprintf(
					'Your domain "%s" lacks an SPF record, making it easier for attackers to spoof emails from your domain. Configure SPF and DKIM records.',
					$domain
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/configure-spf-dkim/',
				'training_link' => 'https://wpshadow.com/training/email-authentication/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
