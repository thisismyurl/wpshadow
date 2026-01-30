<?php
/**
 * Optimole Api Key Diagnostic
 *
 * Optimole Api Key detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.762.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optimole Api Key Diagnostic Class
 *
 * @since 1.762.0000
 */
class Diagnostic_OptimoleApiKey extends Diagnostic_Base {

	protected static $slug = 'optimole-api-key';
	protected static $title = 'Optimole Api Key';
	protected static $description = 'Optimole Api Key detected';
	protected static $family = 'security';

	public static function check() {
		// Check for Optimole
		if ( ! defined( 'OPTIMOLE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API key configured
		$api_key = get_option( 'optimole_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'Optimole API key not configured', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Optimole not connected', 'wpshadow' ),
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/optimole-api-key',
			);
		}
		
		// Check 2: API key in database
		if ( ! defined( 'OPTIMOLE_API_KEY' ) ) {
			$issues[] = __( 'API key in database (should be in wp-config.php)', 'wpshadow' );
		}
		
		// Check 3: Service plan status
		$service_data = get_option( 'optimole_service_data', array() );
		if ( isset( $service_data['limit_reached'] ) && $service_data['limit_reached'] ) {
			$issues[] = __( 'Service limit reached (images not optimizing)', 'wpshadow' );
		}
		
		// Check 4: API connection status
		$connection_status = get_option( 'optimole_connection_status', 'unknown' );
		if ( 'active' !== $connection_status ) {
			$issues[] = sprintf( __( 'API connection: %s (service unavailable)', 'wpshadow' ), $connection_status );
		}
		
		// Check 5: Key exposed in frontend
		$expose_key = get_option( 'optimole_expose_key', false );
		if ( $expose_key ) {
			$issues[] = __( 'API key exposed in HTML (security risk)', 'wpshadow' );
		}
		
		// Check 6: Rate limit warnings
		$rate_limit_hits = get_option( 'optimole_rate_limit_hits', 0 );
		if ( $rate_limit_hits > 10 ) {
			$issues[] = sprintf( __( '%d rate limit hits (API throttling)', 'wpshadow' ), $rate_limit_hits );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 82;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 76;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of API key issues */
				__( 'Optimole API key has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/optimole-api-key',
		);
	}
}
