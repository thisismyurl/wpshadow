<?php
/**
 * Complianz Consent Logging Diagnostic
 *
 * Complianz Consent Logging not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1111.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Complianz Consent Logging Diagnostic Class
 *
 * @since 1.1111.0000
 */
class Diagnostic_ComplianzConsentLogging extends Diagnostic_Base {

	protected static $slug = 'complianz-consent-logging';
	protected static $title = 'Complianz Consent Logging';
	protected static $description = 'Complianz Consent Logging not compliant';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'COMPLIANZ_VERSION' ) && ! function_exists( 'cmplz_get_value' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify consent logging is enabled
		$consent_logging = get_option( 'cmplz_consent_logging', 0 );
		if ( ! $consent_logging ) {
			$issues[] = 'Consent logging not enabled';
		}

		// Check 2: Check for consent proof storage
		$proof_storage = get_option( 'cmplz_proof_of_consent', 0 );
		if ( ! $proof_storage ) {
			$issues[] = 'Proof of consent storage not enabled';
		}

		// Check 3: Verify consent record retention
		$retention_period = get_option( 'cmplz_consent_retention_days', 0 );
		if ( $retention_period < 365 ) {
			$issues[] = 'Consent retention period less than recommended 1 year';
		}

		// Check 4: Check for IP address logging
		$log_ip = get_option( 'cmplz_log_ip_address', 0 );
		if ( ! $log_ip ) {
			$issues[] = 'IP address logging not enabled for consent records';
		}

		// Check 5: Verify consent statistics
		$statistics = get_option( 'cmplz_statistics', 0 );
		if ( ! $statistics ) {
			$issues[] = 'Consent statistics tracking not enabled';
		}

		// Check 6: Check for database cleanup schedule
		$cleanup_scheduled = wp_next_scheduled( 'cmplz_consent_cleanup' );
		if ( ! $cleanup_scheduled ) {
			$issues[] = 'Consent database cleanup not scheduled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Complianz consent logging issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/complianz-consent-logging',
			);
		}

		return null;
	}
}
