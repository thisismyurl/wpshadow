<?php
/**
 * Event Venue Map Performance Diagnostic
 *
 * Event venue maps slowing pages.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.594.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Venue Map Performance Diagnostic Class
 *
 * @since 1.594.0000
 */
class Diagnostic_EventVenueMapPerformance extends Diagnostic_Base {

	protected static $slug = 'event-venue-map-performance';
	protected static $title = 'Event Venue Map Performance';
	protected static $description = 'Event venue maps slowing pages';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) && ! class_exists( 'MEC_Main' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify maps are enabled
		$maps_enabled = get_option( 'event_venue_maps_enabled', 0 );
		if ( ! $maps_enabled ) {
			$issues[] = 'Venue maps not enabled';
		}
		
		// Check 2: Check for Google Maps API key
		$api_key = get_option( 'event_venue_maps_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'Map API key not configured';
		}
		
		// Check 3: Verify lazy loading
		$lazy_load = get_option( 'event_venue_maps_lazyload', 0 );
		if ( ! $lazy_load ) {
			$issues[] = 'Map lazy loading not enabled';
		}
		
		// Check 4: Check for static map fallback
		$static_map = get_option( 'event_venue_maps_static', 0 );
		if ( ! $static_map ) {
			$issues[] = 'Static map fallback not enabled';
		}
		
		// Check 5: Verify map cache
		$map_cache = get_option( 'event_venue_maps_cache', 0 );
		if ( ! $map_cache ) {
			$issues[] = 'Venue map cache not enabled';
		}
		
		// Check 6: Check for map script loading
		$script_load = get_option( 'event_venue_maps_load_footer', 0 );
		if ( ! $script_load ) {
			$issues[] = 'Map scripts not loaded in footer';
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
					'Found %d venue map performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/event-venue-map-performance',
			);
		}
		
		return null;
	}
}
