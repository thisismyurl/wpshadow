<?php
/**
 * Query Monitor Rest Api Calls Diagnostic
 *
 * Query Monitor Rest Api Calls issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1042.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query Monitor Rest Api Calls Diagnostic Class
 *
 * @since 1.1042.0000
 */
class Diagnostic_QueryMonitorRestApiCalls extends Diagnostic_Base {

	protected static $slug = 'query-monitor-rest-api-calls';
	protected static $title = 'Query Monitor Rest Api Calls';
	protected static $description = 'Query Monitor Rest Api Calls issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'QM_VERSION' ) && ! class_exists( 'QM_Dispatcher' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: REST API monitoring enabled
		$rest_enabled = get_option( 'query_monitor_rest_api', 0 );
		if ( ! $rest_enabled ) {
			$issues[] = 'REST API monitoring not enabled';
		}
		
		// Check 2: REST API logging enabled
		$rest_logging = get_option( 'query_monitor_rest_log', 0 );
		if ( ! $rest_logging ) {
			$issues[] = 'REST API logging not enabled';
		}
		
		// Check 3: REST API log retention
		$retention = absint( get_option( 'query_monitor_rest_log_retention', 0 ) );
		if ( $retention <= 0 ) {
			$issues[] = 'REST API log retention not configured';
		}
		
		// Check 4: REST API call limit
		$call_limit = absint( get_option( 'query_monitor_rest_call_limit', 0 ) );
		if ( $call_limit <= 0 ) {
			$issues[] = 'REST API call limit not configured';
		}
		
		// Check 5: Authentication required
		$rest_auth = get_option( 'query_monitor_rest_auth_required', 0 );
		if ( ! $rest_auth ) {
			$issues[] = 'REST API monitoring not restricted to authenticated users';
		}
		
		// Check 6: Stack trace collection enabled
		$rest_traces = get_option( 'query_monitor_rest_traces', 0 );
		if ( ! $rest_traces ) {
			$issues[] = 'REST API stack trace collection not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Query Monitor REST API issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/query-monitor-rest-api-calls',
			);
		}
		
		return null;
	}
}
