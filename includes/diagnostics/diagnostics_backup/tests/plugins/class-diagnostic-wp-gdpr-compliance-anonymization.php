<?php
/**
 * Wp Gdpr Compliance Anonymization Diagnostic
 *
 * Wp Gdpr Compliance Anonymization not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1125.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Gdpr Compliance Anonymization Diagnostic Class
 *
 * @since 1.1125.0000
 */
class Diagnostic_WpGdprComplianceAnonymization extends Diagnostic_Base {

	protected static $slug = 'wp-gdpr-compliance-anonymization';
	protected static $title = 'Wp Gdpr Compliance Anonymization';
	protected static $description = 'Wp Gdpr Compliance Anonymization not compliant';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WPGDPRC\WordPress\Plugin' ) && ! defined( 'WP_GDPR_C_SLUG' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Data anonymization enabled
		$anon_enabled = get_option( 'wp_gdpr_anonymization_enabled', '0' );
		if ( '0' === $anon_enabled ) {
			$issues[] = 'data anonymization not enabled';
		}

		// Check 2: Retention period configured
		$retention_days = get_option( 'wp_gdpr_retention_period', 0 );
		if ( empty( $retention_days ) ) {
			$issues[] = 'no data retention period set (keeps data indefinitely)';
		} elseif ( $retention_days > 730 ) {
			$years = round( $retention_days / 365, 1 );
			$issues[] = "very long retention period ({$years} years)";
		}

		// Check 3: Automated data removal
		$auto_removal = get_option( 'wp_gdpr_auto_removal', '0' );
		if ( '0' === $auto_removal && ! empty( $retention_days ) ) {
			$issues[] = 'retention period set but auto-removal disabled';
		}

		// Check 4: IP address anonymization
		$ip_anon = get_option( 'wp_gdpr_anonymize_ip', '0' );
		if ( '0' === $ip_anon ) {
			$issues[] = 'IP addresses not anonymized';
		}

		// Check 5: Cookie consent
		$consent_banner = get_option( 'wp_gdpr_consent_banner', '0' );
		if ( '0' === $consent_banner ) {
			$issues[] = 'cookie consent banner not enabled';
		}

		// Check 6: Data export/deletion requests
		$pending_requests = wp_count_posts( 'user_request' );
		if ( isset( $pending_requests->request_pending ) && $pending_requests->request_pending > 10 ) {
			$issues[] = $pending_requests->request_pending . ' pending data requests (GDPR compliance at risk)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'GDPR compliance issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-gdpr-compliance-anonymization',
			);
		}

		return null;
	}
}
