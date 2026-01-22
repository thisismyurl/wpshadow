<?php
declare(strict_types=1);
/**
 * Mobile Usability Issues Diagnostic
 *
 * Philosophy: SEO mobile-first - mobile usability is critical
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for mobile usability issues.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Mobile_Usability extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if theme is responsive
		$theme = wp_get_theme();
		$tags = $theme->get( 'Tags' );
		
		if ( ! in_array( 'responsive', array_map( 'strtolower', $tags ), true ) ) {
			return array(
				'id'          => 'seo-mobile-usability',
				'title'       => 'Mobile Usability Issues',
				'description' => 'Theme not marked as responsive. Google uses mobile-first indexing. Ensure site is mobile-friendly with responsive design.',
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-mobile-usability/',
				'training_link' => 'https://wpshadow.com/training/mobile-seo/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}
