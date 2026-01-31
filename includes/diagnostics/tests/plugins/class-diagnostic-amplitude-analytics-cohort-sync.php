<?php
/**
 * Amplitude Analytics Cohort Sync Diagnostic
 *
 * Amplitude Analytics Cohort Sync misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1388.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amplitude Analytics Cohort Sync Diagnostic Class
 *
 * @since 1.1388.0000
 */
class Diagnostic_AmplitudeAnalyticsCohortSync extends Diagnostic_Base {

	protected static $slug = 'amplitude-analytics-cohort-sync';
	protected static $title = 'Amplitude Analytics Cohort Sync';
	protected static $description = 'Amplitude Analytics Cohort Sync misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Amplitude Analytics
		$has_amplitude = defined( 'AMPLITUDE_API_KEY' ) ||
		                 get_option( 'amplitude_api_key', '' ) ||
		                 function_exists( 'amplitude_track_event' );
		
		if ( ! $has_amplitude ) {
			return null;
		}
		
		$api_key = get_option( 'amplitude_api_key', '' );
		if ( empty( $api_key ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Cohort sync enabled
		$cohort_sync = get_option( 'amplitude_cohort_sync', 'no' );
		if ( 'no' === $cohort_sync ) {
			$issues[] = __( 'Cohort sync disabled (segmentation unavailable)', 'wpshadow' );
		}
		
		// Check 2: Cohort definitions
		$cohorts = get_option( 'amplitude_cohorts', array() );
		if ( empty( $cohorts ) && 'yes' === $cohort_sync ) {
			$issues[] = __( 'Cohort sync enabled but no cohorts defined', 'wpshadow' );
		}
		
		// Check 3: Sync frequency
		$sync_frequency = get_option( 'amplitude_sync_frequency', 'daily' );
		if ( 'hourly' === $sync_frequency && count( $cohorts ) > 10 ) {
			$issues[] = sprintf( __( 'Hourly sync with %d cohorts (API rate limit risk)', 'wpshadow' ), count( $cohorts ) );
		}
		
		// Check 4: User property mapping
		$user_properties = get_option( 'amplitude_user_properties', array() );
		if ( count( $user_properties ) > 50 ) {
			$issues[] = sprintf( __( '%d user properties tracked (performance overhead)', 'wpshadow' ), count( $user_properties ) );
		}
		
		// Check 5: Error logging
		$error_logging = get_option( 'amplitude_error_logging', 'off' );
		if ( 'off' === $error_logging ) {
			$issues[] = __( 'Error logging disabled (sync failures undetected)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of cohort sync issues */
				__( 'Amplitude cohort sync has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/amplitude-analytics-cohort-sync',
		);
	}
}
