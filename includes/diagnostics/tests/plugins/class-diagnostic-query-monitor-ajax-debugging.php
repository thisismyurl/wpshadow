<?php
/**
 * Query Monitor Ajax Debugging Diagnostic
 *
 * Query Monitor Ajax Debugging issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1041.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query Monitor Ajax Debugging Diagnostic Class
 *
 * @since 1.1041.0000
 */
class Diagnostic_QueryMonitorAjaxDebugging extends Diagnostic_Base {

	protected static $slug = 'query-monitor-ajax-debugging';
	protected static $title = 'Query Monitor Ajax Debugging';
	protected static $description = 'Query Monitor Ajax Debugging issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Query Monitor
		if ( ! class_exists( 'QueryMonitor' ) && ! defined( 'QM_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: AJAX debugging enabled
		$ajax_debug = get_option( 'qm_enable_ajax_debugging', false );
		if ( ! $ajax_debug ) {
			$issues[] = __( 'AJAX debugging not enabled (limited troubleshooting)', 'wpshadow' );
		}
		
		// Check 2: AJAX error logging
		$log_errors = get_option( 'qm_log_ajax_errors', true );
		if ( ! $log_errors ) {
			$issues[] = __( 'AJAX error logging disabled (errors go unnoticed)', 'wpshadow' );
		}
		
		// Check 3: Query Monitor visible for AJAX
		$show_ajax = get_option( 'qm_show_ajax_requests', false );
		if ( ! $show_ajax && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$issues[] = __( 'Query Monitor hidden during AJAX (no output)', 'wpshadow' );
		}
		
		// Check 4: Response header debugging
		$header_debug = get_option( 'qm_enable_response_headers', true );
		if ( ! $header_debug ) {
			$issues[] = __( 'Response header debugging disabled (limited insights)', 'wpshadow' );
		}
		
		// Check 5: Slow AJAX threshold
		$slow_threshold = get_option( 'qm_slow_ajax_threshold', 1.0 );
		if ( $slow_threshold > 3.0 ) {
			$issues[] = sprintf( __( 'Slow AJAX threshold: %.1fs (missing performance issues)', 'wpshadow' ), $slow_threshold );
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
				/* translators: %s: list of AJAX debugging issues */
				__( 'Query Monitor AJAX debugging has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/query-monitor-ajax-debugging',
		);
	}
}
