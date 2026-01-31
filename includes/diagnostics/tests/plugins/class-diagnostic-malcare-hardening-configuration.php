<?php
/**
 * Malcare Hardening Configuration Diagnostic
 *
 * Malcare Hardening Configuration misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.889.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Malcare Hardening Configuration Diagnostic Class
 *
 * @since 1.889.0000
 */
class Diagnostic_MalcareHardeningConfiguration extends Diagnostic_Base {

	protected static $slug = 'malcare-hardening-configuration';
	protected static $title = 'Malcare Hardening Configuration';
	protected static $description = 'Malcare Hardening Configuration misconfiguration';
	protected static $family = 'security';

	public static function check() {
		// Check for MalCare Security plugin
		$has_malcare = class_exists( 'MalCare_Loader' ) ||
		               defined( 'MALCARE_VERSION' ) ||
		               get_option( 'malcare_api_key', '' ) !== '';
		
		if ( ! $has_malcare ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Firewall enabled
		$firewall = get_option( 'malcare_firewall_enabled', 'no' );
		if ( 'no' === $firewall ) {
			$issues[] = __( 'Firewall disabled (attack protection off)', 'wpshadow' );
		}
		
		// Check 2: Login protection
		$login_protection = get_option( 'malcare_login_protection', 'no' );
		if ( 'no' === $login_protection ) {
			$issues[] = __( 'Login protection disabled (brute force risk)', 'wpshadow' );
		}
		
		// Check 3: File change detection
		$file_monitoring = get_option( 'malcare_file_monitoring', 'no' );
		if ( 'no' === $file_monitoring ) {
			$issues[] = __( 'File monitoring disabled (backdoor risk)', 'wpshadow' );
		}
		
		// Check 4: Malware scan frequency
		$scan_frequency = get_option( 'malcare_scan_frequency', 'daily' );
		if ( 'manual' === $scan_frequency ) {
			$issues[] = __( 'Manual scanning only (delayed detection)', 'wpshadow' );
		}
		
		// Check 5: Hardening applied
		$hardening_options = get_option( 'malcare_hardening', array() );
		$required_hardening = array( 'disable_file_editor', 'hide_wp_version', 'disable_xmlrpc' );
		
		$missing_hardening = array_diff( $required_hardening, array_keys( array_filter( $hardening_options ) ) );
		if ( ! empty( $missing_hardening ) ) {
			$issues[] = sprintf( __( '%d hardening options not applied', 'wpshadow' ), count( $missing_hardening ) );
		}
		
		// Check 6: Backup configuration
		$auto_backup = get_option( 'malcare_auto_backup', 'no' );
		if ( 'no' === $auto_backup ) {
			$issues[] = __( 'Auto-backup disabled (no recovery option)', 'wpshadow' );
		}
		
		// Check 7: Two-factor authentication
		$twofa = get_option( 'malcare_2fa_enabled', 'no' );
		if ( 'no' === $twofa ) {
			$issues[] = __( '2FA not enabled (weak authentication)', 'wpshadow' );
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
				/* translators: %s: list of hardening configuration issues */
				__( 'MalCare has %d hardening issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/malcare-hardening-configuration',
		);
	}
}
