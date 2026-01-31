<?php
/**
 * Matomo Analytics Privacy Settings Diagnostic
 *
 * Matomo Analytics Privacy Settings misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1353.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Matomo Analytics Privacy Settings Diagnostic Class
 *
 * @since 1.1353.0000
 */
class Diagnostic_MatomoAnalyticsPrivacySettings extends Diagnostic_Base {

	protected static $slug = 'matomo-analytics-privacy-settings';
	protected static $title = 'Matomo Analytics Privacy Settings';
	protected static $description = 'Matomo Analytics Privacy Settings misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MATOMO_ANALYTICS_FILE' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Cookie consent.
		$cookie_consent = get_option( 'matomo_cookie_consent', '0' );
		if ( '0' === $cookie_consent ) {
			$issues[] = 'cookie consent not enabled';
		}
		
		// Check 2: IP anonymization.
		$ip_anon = get_option( 'matomo_anonymize_ip', '0' );
		if ( '0' === $ip_anon ) {
			$issues[] = 'IP anonymization disabled';
		}
		
		// Check 3: Do Not Track respect.
		$respect_dnt = get_option( 'matomo_respect_dnt', '1' );
		if ( '0' === $respect_dnt ) {
			$issues[] = 'Do Not Track not respected';
		}
		
		// Check 4: User ID tracking.
		$track_user_id = get_option( 'matomo_track_user_id', '0' );
		if ( '1' === $track_user_id ) {
			$issues[] = 'user ID tracking enabled (privacy risk)';
		}
		
		// Check 5: Data retention.
		$retention = get_option( 'matomo_data_retention', 0 );
		if ( 0 === $retention || $retention > 730 ) {
			$issues[] = 'data retention not set or too long';
		}
		
		// Check 6: GPC signal respect.
		$gpc = get_option( 'matomo_respect_gpc', '0' );
		if ( '0' === $gpc ) {
			$issues[] = 'Global Privacy Control not respected';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 65 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Matomo privacy issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/matomo-analytics-privacy-settings',
			);
		}
		
		return null;
	}
}
