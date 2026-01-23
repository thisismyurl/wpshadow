<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Bot Traffic Detection and Impact (SECURITY-PERF-002)
 *
 * Identifies bot traffic consuming server resources unnecessarily.
 * Philosophy: Show value (#9) - Optimize server for real users, not bots.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Bot_Traffic_Performance extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$bot_ratio    = (float) get_transient( 'wpshadow_bot_traffic_ratio' ); // percent of requests
		$bot_requests = (int) get_transient( 'wpshadow_bot_request_count' );

		if ( $bot_ratio > 30 || $bot_requests > 1000 ) {
			return array(
				'id'            => 'bot-traffic-performance',
				'title'         => sprintf( __( 'High bot traffic detected (%.1f%%)', 'wpshadow' ), $bot_ratio ),
				'description'   => __( 'Bots are consuming server resources. Add bot rate limiting, robots.txt tuning, or CDN-level bot mitigation.', 'wpshadow' ),
				'severity'      => 'medium',
				'category'      => 'other',
				'kb_link'       => 'https://wpshadow.com/kb/bot-traffic-performance/',
				'training_link' => 'https://wpshadow.com/training/bot-mitigation/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
				'bot_requests'  => $bot_requests,
			);
		}

		return null;
	}

}