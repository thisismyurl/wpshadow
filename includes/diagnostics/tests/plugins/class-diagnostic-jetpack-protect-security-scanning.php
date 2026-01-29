<?php
/**
 * Jetpack Protect Security Scanning Diagnostic
 *
 * Jetpack Protect Security Scanning misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.877.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Protect Security Scanning Diagnostic Class
 *
 * @since 1.877.0000
 */
class Diagnostic_JetpackProtectSecurityScanning extends Diagnostic_Base {

	protected static $slug = 'jetpack-protect-security-scanning';
	protected static $title = 'Jetpack Protect Security Scanning';
	protected static $description = 'Jetpack Protect Security Scanning misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Check if Protect module is active
		$protect_active = Jetpack::is_module_active( 'protect' );
		if ( ! $protect_active ) {
			$issues[] = 'protect_module_disabled';
			$threat_level += 35;
		}

		// Check scan module
		$scan_active = Jetpack::is_module_active( 'scan' );
		if ( ! $scan_active ) {
			$issues[] = 'scan_module_disabled';
			$threat_level += 30;
		}

		if ( $scan_active ) {
			// Check last scan time
			$last_scan = get_option( 'jetpack_scan_last_run', 0 );
			if ( $last_scan === 0 || ( time() - $last_scan ) > ( 7 * DAY_IN_SECONDS ) ) {
				$issues[] = 'scan_not_run_recently';
				$threat_level += 25;
			}

			// Check for threats
			$threats = get_option( 'jetpack_scan_threats', array() );
			if ( ! empty( $threats ) ) {
				$issues[] = 'threats_detected';
				$threat_level += 40;
			}
		}

		// Check auto-update settings
		$auto_update = get_option( 'jetpack_protect_auto_update', false );
		if ( ! $auto_update ) {
			$issues[] = 'auto_update_disabled';
			$threat_level += 15;
		}

		// Check whitelist configuration
		$whitelist = get_option( 'jetpack_protect_whitelist', array() );
		if ( empty( $whitelist ) && $protect_active ) {
			$issues[] = 'no_whitelisted_ips';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of security scanning issues */
				__( 'Jetpack Protect security scanning has problems: %s. This leaves your site vulnerable to attacks and malware.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/jetpack-protect-security-scanning',
			);
		}
		
		return null;
	}
}
