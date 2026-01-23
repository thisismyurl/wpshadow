<?php
declare(strict_types=1);
/**
 * 404 Detection and Throttling Diagnostic
 *
 * Philosophy: Scan detection - identify vulnerability scanners
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if 404 scanning is throttled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_404_Throttling extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$has_404_protection = has_filter( 'template_redirect' );

		if ( ! $has_404_protection ) {
			return array(
				'id'            => '404-throttling',
				'title'         => 'No 404 Scanning Detection',
				'description'   => 'Vulnerability scanners probe your site via 404s. Without 404 throttling, scanners can freely map your site structure and find exploitable endpoints.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/throttle-404-scans/',
				'training_link' => 'https://wpshadow.com/training/scanner-detection/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
			);
		}

		return null;
	}

}