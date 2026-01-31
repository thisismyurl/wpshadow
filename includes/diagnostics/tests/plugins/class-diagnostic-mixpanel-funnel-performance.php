<?php
/**
 * Mixpanel Funnel Performance Diagnostic
 *
 * Mixpanel Funnel Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1385.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mixpanel Funnel Performance Diagnostic Class
 *
 * @since 1.1385.0000
 */
class Diagnostic_MixpanelFunnelPerformance extends Diagnostic_Base {

	protected static $slug = 'mixpanel-funnel-performance';
	protected static $title = 'Mixpanel Funnel Performance';
	protected static $description = 'Mixpanel Funnel Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'mixpanel_track' ) && ! defined( 'MIXPANEL_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify Mixpanel token is configured
		$token = get_option( 'mixpanel_token', '' );
		if ( empty( $token ) ) {
			$issues[] = 'Mixpanel token not configured';
		}

		// Check 2: Check for asynchronous tracking
		$async_tracking = get_option( 'mixpanel_async', 0 );
		if ( ! $async_tracking ) {
			$issues[] = 'Asynchronous tracking not enabled (impacts page load)';
		}

		// Check 3: Verify event batching
		$batch_events = get_option( 'mixpanel_batch_events', 0 );
		if ( ! $batch_events ) {
			$issues[] = 'Event batching not enabled (more API calls)';
		}

		// Check 4: Check for user property tracking
		$track_user_props = get_option( 'mixpanel_track_user_properties', 0 );
		if ( ! $track_user_props ) {
			$issues[] = 'User property tracking not configured';
		}

		// Check 5: Verify funnel configuration
		$funnels = get_option( 'mixpanel_funnels', array() );
		if ( empty( $funnels ) ) {
			$issues[] = 'No funnels configured';
		}

		// Check 6: Check for event throttling
		$throttle_events = get_option( 'mixpanel_throttle', 0 );
		if ( ! $throttle_events ) {
			$issues[] = 'Event throttling not enabled (could exceed API limits)';
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
					'Found %d Mixpanel funnel performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/mixpanel-funnel-performance',
			);
		}

		return null;
	}
}
