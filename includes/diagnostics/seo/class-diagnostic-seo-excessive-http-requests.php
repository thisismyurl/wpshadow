<?php
declare(strict_types=1);
/**
 * Excessive HTTP Requests Diagnostic
 *
 * Philosophy: SEO performance - fewer requests = faster load
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for excessive HTTP requests.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Excessive_HTTP_Requests extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_scripts, $wp_styles;
		
		$total_requests = 0;
		
		if ( ! empty( $wp_scripts->queue ) ) {
			$total_requests += count( $wp_scripts->queue );
		}
		
		if ( ! empty( $wp_styles->queue ) ) {
			$total_requests += count( $wp_styles->queue );
		}
		
		if ( $total_requests > 20 ) {
			return array(
				'id'          => 'seo-excessive-http-requests',
				'title'       => 'Excessive HTTP Requests',
				'description' => sprintf( '%d CSS/JS files queued. Each request adds latency. Combine files, remove unused plugins, defer non-critical assets. Target < 15 requests.', $total_requests ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/reduce-http-requests/',
				'training_link' => 'https://wpshadow.com/training/request-optimization/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
