<?php
/**
 * Matomo Analytics Visitor Tracking Diagnostic
 *
 * Matomo Analytics Visitor Tracking misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1355.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Matomo Analytics Visitor Tracking Diagnostic Class
 *
 * @since 1.1355.0000
 */
class Diagnostic_MatomoAnalyticsVisitorTracking extends Diagnostic_Base {

	protected static $slug = 'matomo-analytics-visitor-tracking';
	protected static $title = 'Matomo Analytics Visitor Tracking';
	protected static $description = 'Matomo Analytics Visitor Tracking misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MATOMO_ANALYTICS_FILE' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get Matomo settings
		$matomo_settings = get_option( 'matomo-settings', array() );

		// Check tracking enabled
		$tracking_enabled = isset( $matomo_settings['track_mode'] ) && $matomo_settings['track_mode'] !== 'disabled';
		if ( ! $tracking_enabled ) {
			$issues[] = 'tracking_disabled';
			$threat_level += 30;
		}

		// Check cookie consent
		$require_consent = isset( $matomo_settings['require_consent'] ) ? $matomo_settings['require_consent'] : false;
		if ( ! $require_consent ) {
			$issues[] = 'cookie_consent_not_required';
			$threat_level += 25;
		}

		// Check IP anonymization
		$anonymize_ip = isset( $matomo_settings['anonymize_ip'] ) ? $matomo_settings['anonymize_ip'] : false;
		if ( ! $anonymize_ip ) {
			$issues[] = 'ip_anonymization_disabled';
			$threat_level += 20;
		}

		// Check DoNotTrack
		$respect_dnt = isset( $matomo_settings['respect_dnt'] ) ? $matomo_settings['respect_dnt'] : false;
		if ( ! $respect_dnt ) {
			$issues[] = 'donottrack_not_respected';
			$threat_level += 15;
		}

		// Check tracking code presence
		if ( $tracking_enabled ) {
			$site_id = isset( $matomo_settings['site_id'] ) ? $matomo_settings['site_id'] : '';
			if ( empty( $site_id ) ) {
				$issues[] = 'site_id_not_configured';
				$threat_level += 20;
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of visitor tracking issues */
				__( 'Matomo Analytics visitor tracking is misconfigured: %s. This violates privacy regulations and causes incomplete data collection.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/matomo-analytics-visitor-tracking',
			);
		}
		
		return null;
	}
}
