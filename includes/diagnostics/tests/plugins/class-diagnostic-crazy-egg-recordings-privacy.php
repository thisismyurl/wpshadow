<?php
/**
 * Crazy Egg Recordings Privacy Diagnostic
 *
 * Crazy Egg Recordings Privacy misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1376.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Crazy Egg Recordings Privacy Diagnostic Class
 *
 * @since 1.1376.0000
 */
class Diagnostic_CrazyEggRecordingsPrivacy extends Diagnostic_Base {

	protected static $slug = 'crazy-egg-recordings-privacy';
	protected static $title = 'Crazy Egg Recordings Privacy';
	protected static $description = 'Crazy Egg Recordings Privacy misconfigured';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		// Check 1: Privacy mode enabled
		$privacy = get_option( 'crazy_egg_privacy_mode_enabled', 0 );
		if ( ! $privacy ) {
			$issues[] = 'Privacy mode not enabled';
		}

		// Check 2: PII masking configured
		$masking = get_option( 'crazy_egg_pii_masking_configured', 0 );
		if ( ! $masking ) {
			$issues[] = 'PII masking not configured';
		}

		// Check 3: Sensitive field exclusion
		$exclusion = get_option( 'crazy_egg_sensitive_fields_excluded', 0 );
		if ( ! $exclusion ) {
			$issues[] = 'Sensitive field exclusion not enabled';
		}

		// Check 4: GDPR compliance
		$gdpr = get_option( 'crazy_egg_gdpr_compliance_enabled', 0 );
		if ( ! $gdpr ) {
			$issues[] = 'GDPR compliance mode not enabled';
		}

		// Check 5: Consent management
		$consent = get_option( 'crazy_egg_consent_management_enabled', 0 );
		if ( ! $consent ) {
			$issues[] = 'Consent management not configured';
		}

		// Check 6: Data retention policy
		$retention = get_option( 'crazy_egg_data_retention_policy_set', 0 );
		if ( ! $retention ) {
			$issues[] = 'Data retention policy not set';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d privacy issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/crazy-egg-recordings-privacy',
			);
		}

		return null;
	}
}
