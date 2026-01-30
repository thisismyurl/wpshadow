<?php
/**
 * Sucuri Firewall Configuration Diagnostic
 *
 * Sucuri Firewall Configuration misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.850.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sucuri Firewall Configuration Diagnostic Class
 *
 * @since 1.850.0000
 */
class Diagnostic_SucuriFirewallConfiguration extends Diagnostic_Base {

	protected static $slug = 'sucuri-firewall-configuration';
	protected static $title = 'Sucuri Firewall Configuration';
	protected static $description = 'Sucuri Firewall Configuration misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'SUCURISCAN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Firewall API key
		$api_key = get_option( 'sucuriscan_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'No API key (firewall disabled)', 'wpshadow' );
		}
		
		// Check 2: Malware scanning
		$scan_frequency = get_option( 'sucuriscan_scan_frequency', 'twicedaily' );
		if ( 'never' === $scan_frequency ) {
			$issues[] = __( 'Malware scanning disabled', 'wpshadow' );
		}
		
		// Check 3: Hardening applied
		$hardening = get_option( 'sucuriscan_hardening', array() );
		$required_hardening = array( 'disable_file_editor', 'block_php_upload', 'prevent_listing' );
		
		$missing = array_diff( $required_hardening, array_keys( array_filter( $hardening ) ) );
		if ( ! empty( $missing ) ) {
			$issues[] = sprintf( __( '%d hardening options not applied', 'wpshadow' ), count( $missing ) );
		}
		
		// Check 4: Audit logging
		$audit_log = get_option( 'sucuriscan_audit_report', 'disabled' );
		if ( 'disabled' === $audit_log ) {
			$issues[] = __( 'Audit logging disabled (no activity trail)', 'wpshadow' );
		}
		
		// Check 5: Email alerts
		$email_alerts = get_option( 'sucuriscan_emails_recipients', '' );
		if ( empty( $email_alerts ) ) {
			$issues[] = __( 'No email alerts (silent attacks)', 'wpshadow' );
		}
		
		// Check 6: Failed login monitoring
		$failed_logins = get_option( 'sucuriscan_failed_logins', 'disabled' );
		if ( 'disabled' === $failed_logins ) {
			$issues[] = __( 'Failed login monitoring off (brute force undetected)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 75;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 87;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 81;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of Sucuri firewall issues */
				__( 'Sucuri firewall has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/sucuri-firewall-configuration',
		);
	}
}
