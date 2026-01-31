<?php
/**
 * GeoDirectory Location Security Diagnostic
 *
 * GeoDirectory location data exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.551.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Location Security Diagnostic Class
 *
 * @since 1.551.0000
 */
class Diagnostic_GeodirectoryLocationSecurity extends Diagnostic_Base {

	protected static $slug = 'geodirectory-location-security';
	protected static $title = 'GeoDirectory Location Security';
	protected static $description = 'GeoDirectory location data exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'geodir_init' ) && ! class_exists( 'GeoDir' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Location privacy setting
		$privacy_enabled = get_option( 'geodir_location_privacy', 0 );
		if ( ! $privacy_enabled ) {
			$issues[] = 'Location privacy not enabled';
		}

		// Check 2: Exact address display
		$show_exact = get_option( 'geodir_show_exact_address', 1 );
		if ( $show_exact ) {
			$issues[] = 'Exact address display enabled';
		}

		// Check 3: Geocoding restriction
		$geocode_restrict = get_option( 'geodir_geocode_restrict', 0 );
		if ( ! $geocode_restrict ) {
			$issues[] = 'Geocoding restriction not enabled';
		}

		// Check 4: Map access control
		$map_access = get_option( 'geodir_map_access_control', 0 );
		if ( ! $map_access ) {
			$issues[] = 'Map access control not enabled';
		}

		// Check 5: Masking API keys
		$mask_keys = get_option( 'geodir_mask_api_keys', 0 );
		if ( ! $mask_keys ) {
			$issues[] = 'API keys not masked in admin';
		}

		// Check 6: Logging of location requests
		$log_requests = get_option( 'geodir_location_logging', 0 );
		if ( $log_requests ) {
			$issues[] = 'Location request logging enabled';
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
					'Found %d GeoDirectory location security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/geodirectory-location-security',
			);
		}

		return null;
	}
}
