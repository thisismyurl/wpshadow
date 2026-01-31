<?php
/**
 * Amplitude Analytics User Properties Diagnostic
 *
 * Amplitude Analytics User Properties misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1387.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amplitude Analytics User Properties Diagnostic Class
 *
 * @since 1.1387.0000
 */
class Diagnostic_AmplitudeAnalyticsUserProperties extends Diagnostic_Base {

	protected static $slug = 'amplitude-analytics-user-properties';
	protected static $title = 'Amplitude Analytics User Properties';
	protected static $description = 'Amplitude Analytics User Properties misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		$has_amplitude = defined( 'AMPLITUDE_API_KEY' ) ||
		                 get_option( 'amplitude_api_key', '' ) ||
		                 function_exists( 'amplitude_track_event' );

		if ( ! $has_amplitude ) {
			return null;
		}

		$issues = array();

		// Check 1: User property limit.
		$user_properties = get_option( 'amplitude_user_properties', array() );
		$prop_count = is_array( $user_properties ) ? count( $user_properties ) : 0;
		if ( $prop_count > 100 ) {
			$issues[] = "tracking {$prop_count} user properties (exceeds limit, some may be ignored)";
		}

		// Check 2: PII tracking disabled.
		$track_pii = get_option( 'amplitude_track_pii', '0' );
		if ( '1' === $track_pii ) {
			$issues[] = 'PII tracking enabled (privacy/compliance risk)';
		}

		// Check 3: Property sanitization.
		$sanitize = get_option( 'amplitude_sanitize_properties', '1' );
		if ( '0' === $sanitize ) {
			$issues[] = 'property sanitization disabled (malicious data accepted)';
		}

		// Check 4: Property mapping.
		$property_maps = get_option( 'amplitude_property_mappings', array() );
		if ( empty( $property_maps ) || ! is_array( $property_maps ) ) {
			$issues[] = 'no property mapping configured (default mappings only)';
		}

		// Check 5: Property update frequency.
		$update_freq = get_option( 'amplitude_property_update_frequency', 'session' );
		if ( 'every_event' === $update_freq ) {
			$issues[] = 'updating properties every event (performance overhead)';
		}

		// Check 6: Reserved property names.
		$reserved = array( 'id', 'user_id', 'user_properties', 'app_version' );
		$conflicts = array_intersect( $reserved, array_keys( (array) $user_properties ) );
		if ( ! empty( $conflicts ) ) {
			$issues[] = sprintf( 'using reserved property names: %s', implode( ', ', $conflicts ) );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 50 + ( count( $issues ) * 4 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Amplitude user property issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/amplitude-analytics-user-properties',
			);
		}

		return null;
	}
}
