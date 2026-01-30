<?php
/**
 * Cookiebot Tcf Compliance Diagnostic
 *
 * Cookiebot Tcf Compliance not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1117.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookiebot Tcf Compliance Diagnostic Class
 *
 * @since 1.1117.0000
 */
class Diagnostic_CookiebotTcfCompliance extends Diagnostic_Base {

	protected static $slug = 'cookiebot-tcf-compliance';
	protected static $title = 'Cookiebot Tcf Compliance';
	protected static $description = 'Cookiebot Tcf Compliance not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'COOKIEBOT_VERSION' ) && ! function_exists( 'cookiebot_active' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify TCF 2.0 is enabled
		$tcf_enabled = get_option( 'cookiebot_tcf', 0 );
		if ( ! $tcf_enabled ) {
			$issues[] = 'IAB TCF 2.0 framework not enabled';
		}
		
		// Check 2: Check for CMP ID configuration
		$cmp_id = get_option( 'cookiebot_cmp_id', '' );
		if ( empty( $cmp_id ) ) {
			$issues[] = 'CMP ID not configured';
		}
		
		// Check 3: Verify vendor list is up to date
		$vendor_list_version = get_option( 'cookiebot_tcf_vendor_list_version', 0 );
		if ( $vendor_list_version < 2 ) {
			$issues[] = 'TCF vendor list not updated to v2';
		}
		
		// Check 4: Check for consent string storage
		$consent_storage = get_option( 'cookiebot_consent_storage', '' );
		if ( empty( $consent_storage ) ) {
			$issues[] = 'TCF consent string storage not configured';
		}
		
		// Check 5: Verify legitimate interest configuration
		$legitimate_interest = get_option( 'cookiebot_legitimate_interest', 0 );
		if ( ! $legitimate_interest ) {
			$issues[] = 'Legitimate interest purposes not configured';
		}
		
		// Check 6: Check for Google consent mode integration
		$google_consent = get_option( 'cookiebot_google_consent_mode', 0 );
		if ( ! $google_consent ) {
			$issues[] = 'Google Consent Mode v2 not enabled';
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
					'Found %d Cookiebot TCF compliance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cookiebot-tcf-compliance',
			);
		}
		
		return null;
	}
}
