<?php
/**
 * Cookie Law Info Consent Management Diagnostic
 *
 * Cookie Law Info Consent Management not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1113.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Law Info Consent Management Diagnostic Class
 *
 * @since 1.1113.0000
 */
class Diagnostic_CookieLawInfoConsentManagement extends Diagnostic_Base {

	protected static $slug = 'cookie-law-info-consent-management';
	protected static $title = 'Cookie Law Info Consent Management';
	protected static $description = 'Cookie Law Info Consent Management not compliant';
	protected static $family = 'security';

	public static function check() {
		// Check for Cookie Law Info plugin
		if ( ! defined( 'CLI_VERSION' ) && ! class_exists( 'Cookie_Law_Info' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Consent logging enabled
		$log_consent = get_option( 'cli_log_consent', false );
		if ( ! $log_consent ) {
			$issues[] = __( 'Consent logging not enabled (GDPR proof of consent)', 'wpshadow' );
		}
		
		// Check 2: Consent revocation option
		$allow_revoke = get_option( 'cli_allow_revoke', false );
		if ( ! $allow_revoke ) {
			$issues[] = __( 'Consent revocation not available to users', 'wpshadow' );
		}
		
		// Check 3: Consent record retention
		$consent_records = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}cli_consent_log"
		);
		
		if ( $log_consent && $consent_records === 0 ) {
			$issues[] = __( 'Consent logging enabled but no records found', 'wpshadow' );
		}
		
		// Check 4: Policy version tracking
		$track_version = get_option( 'cli_track_policy_version', false );
		if ( ! $track_version ) {
			$issues[] = __( 'Cookie policy version tracking not enabled', 'wpshadow' );
		}
		
		// Check 5: Audit trail
		$audit_trail = get_option( 'cli_audit_trail_enabled', false );
		if ( ! $audit_trail ) {
			$issues[] = __( 'Consent audit trail not enabled (compliance risk)', 'wpshadow' );
		}
		
		// Check 6: Consent expiration
		$consent_expiry = get_option( 'cli_consent_expiry', 0 );
		if ( $consent_expiry === 0 || $consent_expiry > 365 ) {
			$issues[] = sprintf( __( 'Consent expiry: %d days (GDPR recommends annual refresh)', 'wpshadow' ), $consent_expiry );
		}
		
		// Check 7: Data export functionality
		$data_export = get_option( 'cli_consent_export_enabled', false );
		if ( ! $data_export && $log_consent ) {
			$issues[] = __( 'Consent data export not available (GDPR requirement)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 85;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 78;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of compliance issues */
				__( 'Cookie Law Info consent management has %d compliance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cookie-law-info-consent-management',
		);
	}
}
