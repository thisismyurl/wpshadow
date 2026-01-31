<?php
/**
 * Gdpr Cookie Compliance Geolocation Diagnostic
 *
 * Gdpr Cookie Compliance Geolocation not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1108.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gdpr Cookie Compliance Geolocation Diagnostic Class
 *
 * @since 1.1108.0000
 */
class Diagnostic_GdprCookieComplianceGeolocation extends Diagnostic_Base {

	protected static $slug = 'gdpr-cookie-compliance-geolocation';
	protected static $title = 'Gdpr Cookie Compliance Geolocation';
	protected static $description = 'Gdpr Cookie Compliance Geolocation not compliant';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'gdpr_cookie_is_accepted' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify geolocation feature is enabled
		$geolocation_enabled = get_option( 'gdpr_cookie_geolocation_enabled', false );
		if ( ! $geolocation_enabled ) {
			$issues[] = __( 'Geolocation-based consent not enabled', 'wpshadow' );
		}

		// Check 2: Check IP geolocation database updates
		$last_db_update = get_option( 'gdpr_cookie_geo_db_last_update', 0 );
		if ( $last_db_update < ( time() - ( 90 * DAY_IN_SECONDS ) ) ) {
			$issues[] = __( 'Geolocation database not updated in 90 days', 'wpshadow' );
		}

		// Check 3: Verify privacy mode for IP addresses
		$anonymize_ip = get_option( 'gdpr_cookie_anonymize_ip', false );
		if ( ! $anonymize_ip ) {
			$issues[] = __( 'IP address anonymization not enabled', 'wpshadow' );
		}

		// Check 4: Check geolocation data retention policy
		$geo_data_retention = get_option( 'gdpr_cookie_geo_data_retention', 0 );
		if ( $geo_data_retention > 365 || $geo_data_retention === 0 ) {
			$issues[] = __( 'Geolocation data retention period too long', 'wpshadow' );
		}

		// Check 5: Verify SSL for geolocation requests
		if ( ! is_ssl() ) {
			$issues[] = __( 'SSL not enabled for geolocation data transmission', 'wpshadow' );
		}

		// Check 6: Check consent logging for geolocation
		$log_consent = get_option( 'gdpr_cookie_log_geo_consent', false );
		if ( ! $log_consent ) {
			$issues[] = __( 'Geolocation-based consent logging not enabled', 'wpshadow' );
		}
		return null;
	}
}
