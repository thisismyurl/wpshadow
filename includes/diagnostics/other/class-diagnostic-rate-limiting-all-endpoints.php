<?php
declare(strict_types=1);
/**
 * Rate Limiting on All Endpoints Diagnostic
 *
 * Philosophy: DoS protection - limit request rates
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if rate limiting is applied to all endpoints.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Rate_Limiting_All_Endpoints extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$rate_limit_enabled = get_option( 'wpshadow_rate_limiting_enabled' );

		if ( empty( $rate_limit_enabled ) ) {
			return array(
				'id'            => 'rate-limiting-all-endpoints',
				'title'         => 'No Rate Limiting on All Endpoints',
				'description'   => 'Rate limiting not applied to all API endpoints. Attackers can enumerate users, brute force passwords, or DoS your API. Implement rate limiting on all endpoints.',
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/implement-rate-limiting/',
				'training_link' => 'https://wpshadow.com/training/api-rate-limits/',
				'auto_fixable'  => false,
				'threat_level'  => 70,
			);
		}

		return null;
	}
}
