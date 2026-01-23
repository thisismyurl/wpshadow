<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: HTTP Keep-Alive Reuse and Connection Coalescing (NETWORK-304)
 *
 * Assesses keep-alive reuse and H2/H3 connection coalescing efficiency.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_HttpKeepAliveReuse extends Diagnostic_Base {
	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$reuse_rate         = (float) get_transient( 'wpshadow_keepalive_reuse_rate' ); // percentage
		$coalescing_success = (float) get_transient( 'wpshadow_h2_coalescing_success' ); // percentage

		if ( ( $reuse_rate > 0 && $reuse_rate < 60 ) || ( $coalescing_success > 0 && $coalescing_success < 60 ) ) {
			return array(
				'id'                   => 'http-keep-alive-reuse',
				'title'                => __( 'Keep-alive reuse is low', 'wpshadow' ),
				'description'          => __( 'Connections are not being reused efficiently. Ensure CDN origin coalescing, correct TLS certs for coalescing, and fewer domains.', 'wpshadow' ),
				'severity'             => 'medium',
				'category'             => 'other',
				'kb_link'              => 'https://wpshadow.com/kb/http-keep-alive/',
				'training_link'        => 'https://wpshadow.com/training/http-optimization/',
				'auto_fixable'         => false,
				'threat_level'         => 45,
				'keepalive_reuse_rate' => $reuse_rate,
				'coalescing_success'   => $coalescing_success,
			);
		}

		return null;
	}

}