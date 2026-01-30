<?php
/**
 * Real Cookie Banner Consent Statistics Diagnostic
 *
 * Real Cookie Banner Consent Statistics not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1118.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Real Cookie Banner Consent Statistics Diagnostic Class
 *
 * @since 1.1118.0000
 */
class Diagnostic_RealCookieBannerConsentStatistics extends Diagnostic_Base {

	protected static $slug = 'real-cookie-banner-consent-statistics';
	protected static $title = 'Real Cookie Banner Consent Statistics';
	protected static $description = 'Real Cookie Banner Consent Statistics not compliant';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'RealCookieBanner' ) && ! get_option( 'real_cookie_banner_settings', array() ) ) {
			return null;
		}
		
		$issues = array();
		$settings = get_option( 'real_cookie_banner_settings', array() );
		
		// Check 1: Consent statistics enabled
		$stats_enabled = isset( $settings['consent_statistics'] ) ? (bool) $settings['consent_statistics'] : false;
		if ( ! $stats_enabled ) {
			$issues[] = 'Consent statistics not enabled';
		}
		
		// Check 2: IP anonymization
		$ip_anonymize = isset( $settings['anonymize_ip'] ) ? (bool) $settings['anonymize_ip'] : false;
		if ( ! $ip_anonymize ) {
			$issues[] = 'IP anonymization not enabled';
		}
		
		// Check 3: Retention period
		$retention_days = isset( $settings['consent_retention_days'] ) ? absint( $settings['consent_retention_days'] ) : 0;
		if ( $retention_days <= 0 ) {
			$issues[] = 'Consent retention period not configured';
		}
		
		// Check 4: Consent log encryption
		$log_encryption = isset( $settings['consent_log_encryption'] ) ? (bool) $settings['consent_log_encryption'] : false;
		if ( ! $log_encryption ) {
			$issues[] = 'Consent log encryption not enabled';
		}
		
		// Check 5: Access control for logs
		$log_cap = isset( $settings['consent_log_capability'] ) ? $settings['consent_log_capability'] : '';
		if ( empty( $log_cap ) || 'manage_options' !== $log_cap ) {
			$issues[] = 'Consent log access control not restricted to admins';
		}
		
		// Check 6: Consent stats export
		$export_enabled = isset( $settings['consent_export_enabled'] ) ? (bool) $settings['consent_export_enabled'] : false;
		if ( ! $export_enabled ) {
			$issues[] = 'Consent statistics export not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 55;
			$threat_multiplier = 6;
			$max_threat = 85;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d consent statistics compliance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/real-cookie-banner-consent-statistics',
			);
		}
		
		return null;
	}
}
