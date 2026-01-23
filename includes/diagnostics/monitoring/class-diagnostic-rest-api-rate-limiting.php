<?php
declare(strict_types=1);
/**
 * REST API Rate Limiting Diagnostic
 *
 * Philosophy: DoS prevention - limit API request rates
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if REST API has rate limiting.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_REST_API_Rate_Limiting extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for rate limiting plugins/features
		$rate_limit_plugins = array(
			'wordfence/wordfence.php',
			'wp-rest-api-controller/wp-rest-api-controller.php',
			'disable-json-api/disable-json-api.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		$has_rate_limiting = false;
		
		foreach ( $rate_limit_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				$has_rate_limiting = true;
				break;
			}
		}
		
		// Check if custom rate limiting filter exists
		if ( has_filter( 'rest_authentication_errors' ) ) {
			$has_rate_limiting = true;
		}
		
		if ( ! $has_rate_limiting ) {
			return array(
				'id'          => 'rest-api-rate-limiting',
				'title'       => 'REST API Lacks Rate Limiting',
				'description' => 'Your REST API has no rate limiting, allowing unlimited requests. This enables brute force attacks and denial of service. Implement rate limiting to protect your API.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/rest-api-rate-limiting/',
				'training_link' => 'https://wpshadow.com/training/api-security/',
				'auto_fixable' => false,
				'threat_level' => 65,
			);
		}
		
		return null;
	}

}