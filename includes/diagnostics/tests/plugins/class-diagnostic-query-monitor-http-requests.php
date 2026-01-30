<?php
/**
 * Query Monitor Http Requests Diagnostic
 *
 * Query Monitor Http Requests not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.931.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query Monitor Http Requests Diagnostic Class
 *
 * @since 1.931.0000
 */
class Diagnostic_QueryMonitorHttpRequests extends Diagnostic_Base {

	protected static $slug = 'query-monitor-http-requests';
	protected static $title = 'Query Monitor Http Requests';
	protected static $description = 'Query Monitor Http Requests not optimized';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();
		
		// Check 1: HTTP request tracking
		$tracking = get_option( 'qm_http_request_tracking_enabled', 0 );
		if ( ! $tracking ) {
			$issues[] = 'HTTP request tracking not enabled';
		}
		
		// Check 2: Request deduplication
		$dedup = get_option( 'qm_http_deduplication_enabled', 0 );
		if ( ! $dedup ) {
			$issues[] = 'Request deduplication not enabled';
		}
		
		// Check 3: Slow request detection
		$slow = get_option( 'qm_slow_request_detection_enabled', 0 );
		if ( ! $slow ) {
			$issues[] = 'Slow request detection not enabled';
		}
		
		// Check 4: Request timeout configuration
		$timeout = absint( get_option( 'qm_http_request_timeout_ms', 0 ) );
		if ( $timeout <= 0 ) {
			$issues[] = 'Request timeout not configured';
		}
		
		// Check 5: Blocking request detection
		$blocking = get_option( 'qm_blocking_request_detection_enabled', 0 );
		if ( ! $blocking ) {
			$issues[] = 'Blocking request detection not enabled';
		}
		
		// Check 6: Request filtering
		$filter = get_option( 'qm_http_request_filtering_enabled', 0 );
		if ( ! $filter ) {
			$issues[] = 'Request filtering not configured';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 30;
			$threat_multiplier = 6;
			$max_threat = 60;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d HTTP request issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/query-monitor-http-requests',
			);
		}
		
		return null;
	}
}
