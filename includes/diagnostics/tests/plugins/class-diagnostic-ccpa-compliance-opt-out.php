<?php
/**
 * Ccpa Compliance Opt Out Diagnostic
 *
 * Ccpa Compliance Opt Out not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1133.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ccpa Compliance Opt Out Diagnostic Class
 *
 * @since 1.1133.0000
 */
class Diagnostic_CcpaComplianceOptOut extends Diagnostic_Base {

	protected static $slug = 'ccpa-compliance-opt-out';
	protected static $title = 'Ccpa Compliance Opt Out';
	protected static $description = 'Ccpa Compliance Opt Out not compliant';
	protected static $family = 'security';

	public static function check() {
		$issues = array();
		
		// Check 1: CCPA opt-out link visible
		$opt_out_link = get_option( 'ccpa_opt_out_link_enabled', false );
		if ( ! $opt_out_link ) {
			$issues[] = 'CCPA opt-out link not visible';
		}
		
		// Check 2: Data deletion process configured
		$data_deletion = get_option( 'ccpa_data_deletion_enabled', false );
		if ( ! $data_deletion ) {
			$issues[] = 'Data deletion process not configured';
		}
		
		// Check 3: Privacy policy includes CCPA rights
		$privacy_policy = get_option( 'ccpa_privacy_policy_updated', false );
		if ( ! $privacy_policy ) {
			$issues[] = 'Privacy policy missing CCPA rights';
		}
		
		// Check 4: Opt-out tracking enabled
		$opt_out_tracking = get_option( 'ccpa_opt_out_tracking', false );
		if ( ! $opt_out_tracking ) {
			$issues[] = 'Opt-out tracking disabled';
		}
		
		// Check 5: User verification for opt-out
		$verification = get_option( 'ccpa_opt_out_verification', false );
		if ( ! $verification ) {
			$issues[] = 'User verification not required';
		}
		
		// Check 6: Consumer rights disclosure
		$rights_disclosure = get_option( 'ccpa_rights_disclosure', false );
		if ( ! $rights_disclosure ) {
			$issues[] = 'Consumer rights not disclosed';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 60 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'CCPA compliance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ccpa-compliance-opt-out',
			);
		}
		
		return null;
	}
}
