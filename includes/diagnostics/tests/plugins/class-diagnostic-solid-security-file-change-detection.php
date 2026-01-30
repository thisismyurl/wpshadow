<?php
/**
 * Solid Security File Change Detection Diagnostic
 *
 * Solid Security File Change Detection misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.882.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Solid Security File Change Detection Diagnostic Class
 *
 * @since 1.882.0000
 */
class Diagnostic_SolidSecurityFileChangeDetection extends Diagnostic_Base {

	protected static $slug = 'solid-security-file-change-detection';
	protected static $title = 'Solid Security File Change Detection';
	protected static $description = 'Solid Security File Change Detection misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'itsec_load_textdomain' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: File change detection enabled
		$fcd_enabled = get_option( 'itsec_file_change_enabled', false );
		if ( ! $fcd_enabled ) {
			return null;
		}
		
		// Check 2: Scan frequency
		$scan_frequency = get_option( 'itsec_file_change_frequency', 'daily' );
		if ( 'hourly' === $scan_frequency ) {
			$issues[] = __( 'Hourly scans (server resource intensive)', 'wpshadow' );
		}
		
		// Check 3: Excluded directories
		$excluded_dirs = get_option( 'itsec_file_change_excluded', array() );
		$required_exclusions = array( 'cache', 'uploads', 'logs', 'tmp' );
		
		$missing = array_diff( $required_exclusions, $excluded_dirs );
		if ( count( $missing ) > 0 ) {
			$issues[] = sprintf(
				/* translators: %s: list of directories */
				__( 'Missing exclusions: %s (false positives)', 'wpshadow' ),
				implode( ', ', $missing )
			);
		}
		
		// Check 4: File baseline established
		$baseline = get_option( 'itsec_file_change_baseline', false );
		if ( ! $baseline ) {
			$issues[] = __( 'No file baseline (all files flagged as changed)', 'wpshadow' );
		}
		
		// Check 5: Email notifications
		$email_notify = get_option( 'itsec_file_change_email', true );
		$email_recipients = get_option( 'itsec_notification_email', array() );
		
		if ( $email_notify && empty( $email_recipients ) ) {
			$issues[] = __( 'Notifications enabled but no recipients (alerts lost)', 'wpshadow' );
		}
		
		// Check 6: Large site scan performance
		$file_count = get_option( 'itsec_file_change_count', 0 );
		if ( $file_count > 50000 ) {
			$issues[] = sprintf( __( '%s files being scanned (performance impact)', 'wpshadow' ), number_format_i18n( $file_count ) );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 82;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 76;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of file change detection issues */
				__( 'Solid Security file change detection has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/solid-security-file-change-detection',
		);
	}
}
