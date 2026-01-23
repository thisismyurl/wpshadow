<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: HTTP/2/3 Prioritization Contention (NETWORK-325)
 *
 * Detects resource contention and poor prioritization on H2/H3.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_H2PrioritizationContention extends Diagnostic_Base {
	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$priority_score  = (int) get_transient( 'wpshadow_h2_prioritization_score' ); // 0-100 higher is better
		$blocked_streams = (int) get_transient( 'wpshadow_h2_blocked_streams' );

		if ( $priority_score > 0 && $priority_score < 70 || $blocked_streams > 5 ) {
			return array(
				'id'              => 'h2-prioritization-contention',
				'title'           => __( 'H2/H3 prioritization contention detected', 'wpshadow' ),
				'description'     => __( 'Resource prioritization over HTTP/2/3 is suboptimal. Review preload/fetchpriority and consolidate critical resources.', 'wpshadow' ),
				'severity'        => 'medium',
				'category'        => 'other',
				'kb_link'         => 'https://wpshadow.com/kb/h2-prioritization/',
				'training_link'   => 'https://wpshadow.com/training/http2-performance/',
				'auto_fixable'    => false,
				'threat_level'    => 50,
				'priority_score'  => $priority_score,
				'blocked_streams' => $blocked_streams,
			);
		}

		return null;
	}

}