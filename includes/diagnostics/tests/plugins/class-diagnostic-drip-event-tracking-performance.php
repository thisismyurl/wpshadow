<?php
/**
 * Drip Event Tracking Performance Diagnostic
 *
 * Drip Event Tracking Performance configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.738.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Drip Event Tracking Performance Diagnostic Class
 *
 * @since 1.738.0000
 */
class Diagnostic_DripEventTrackingPerformance extends Diagnostic_Base {

	protected static $slug = 'drip-event-tracking-performance';
	protected static $title = 'Drip Event Tracking Performance';
	protected static $description = 'Drip Event Tracking Performance configuration issues';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Drip_Api' ) && ! function_exists( 'drip_connect' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify Drip account ID is configured
		$account_id = get_option( 'drip_account_id', '' );
		if ( empty( $account_id ) ) {
			$issues[] = 'Drip account ID not configured';
		}

		// Check 2: Check for API token security
		$api_token = get_option( 'drip_api_token', '' );
		if ( empty( $api_token ) ) {
			$issues[] = 'Drip API token not configured';
		}

		// Check 3: Verify asynchronous event tracking
		$async_tracking = get_option( 'drip_async_tracking', 0 );
		if ( ! $async_tracking ) {
			$issues[] = 'Asynchronous event tracking not enabled (impacts performance)';
		}

		// Check 4: Check for event batching
		$batch_events = get_option( 'drip_batch_events', 0 );
		if ( ! $batch_events ) {
			$issues[] = 'Event batching not enabled (more API requests)';
		}

		// Check 5: Verify rate limiting
		$rate_limit = get_option( 'drip_rate_limit', 0 );
		if ( ! $rate_limit ) {
			$issues[] = 'API rate limiting not configured';
		}

		// Check 6: Check for cache integration
		$cache_enabled = get_option( 'drip_cache_enabled', 0 );
		if ( ! $cache_enabled ) {
			$issues[] = 'Response caching not enabled';
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
					'Found %d Drip event tracking performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/drip-event-tracking-performance',
			);
		}

		return null;
	}
}
