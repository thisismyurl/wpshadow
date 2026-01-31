<?php
/**
 * Advanced Ads Tracking Privacy Diagnostic
 *
 * Ad tracking not GDPR compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.293.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Tracking Privacy Diagnostic Class
 *
 * @since 1.293.0000
 */
class Diagnostic_AdvancedAdsTrackingPrivacy extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-tracking-privacy';
	protected static $title = 'Advanced Ads Tracking Privacy';
	protected static $description = 'Ad tracking not GDPR compliant';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: User tracking without consent.
		$tracking_enabled = get_option( 'advads_tracking_enabled', '0' );
		$consent_integration = get_option( 'advads_consent_integration', 'none' );
		if ( '1' === $tracking_enabled && 'none' === $consent_integration ) {
			$issues[] = 'user tracking enabled without GDPR consent integration';
		}

		// Check 2: Cookie policy disclosure.
		$cookie_notice = get_option( 'advads_cookie_notice', '0' );
		if ( '0' === $cookie_notice && '1' === $tracking_enabled ) {
			$issues[] = 'tracking cookies used without disclosure notice';
		}

		// Check 3: Privacy policy integration.
		$privacy_policy = get_option( 'advads_privacy_policy_link', '' );
		if ( empty( $privacy_policy ) && '1' === $tracking_enabled ) {
			$issues[] = 'no privacy policy link configured for ad tracking';
		}

		// Check 4: IP address anonymization.
		$ip_anonymization = get_option( 'advads_anonymize_ip', '0' );
		if ( '0' === $ip_anonymization && '1' === $tracking_enabled ) {
			$issues[] = 'IP addresses not anonymized (GDPR requirement)';
		}

		// Check 5: Third-party tracking scripts.
		global $wpdb;
		$external_tracking = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_content LIKE %s",
				'advanced_ads',
				'%google-analytics%'
			)
		);
		if ( $external_tracking > 0 && 'none' === $consent_integration ) {
			$issues[] = "{$external_tracking} ads with third-party tracking (requires user consent)";
		}

		// Check 6: Data retention policy.
		$data_retention = get_option( 'advads_data_retention_days', 0 );
		if ( 0 === $data_retention && '1' === $tracking_enabled ) {
			$issues[] = 'no data retention policy configured (GDPR compliance issue)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Advanced Ads tracking privacy issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-tracking-privacy',
			);
		}

		return null;
	}
}
