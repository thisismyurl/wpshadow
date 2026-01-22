<?php
declare(strict_types=1);
/**
 * Slow Page Speed Diagnostic
 *
 * Philosophy: SEO Core Web Vitals - speed is ranking factor
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for slow page speed.
 */
class Diagnostic_SEO_Slow_Page_Speed extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$home_url = home_url( '/' );
		$start = microtime( true );
		$response = wp_remote_get( $home_url, array( 'timeout' => 10 ) );
		$load_time = microtime( true ) - $start;
		
		if ( ! is_wp_error( $response ) && $load_time > 3 ) {
			return array(
				'id'          => 'seo-slow-page-speed',
				'title'       => 'Slow Page Speed',
				'description' => sprintf( 'Homepage loads in %.2f seconds. Google recommends under 2.5s. Page speed is a ranking factor. Optimize images, enable caching, minify CSS/JS.', $load_time ),
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/improve-page-speed/',
				'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}
		
		return null;
	}
}
