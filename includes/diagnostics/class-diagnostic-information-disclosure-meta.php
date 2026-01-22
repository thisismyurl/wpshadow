<?php declare(strict_types=1);
/**
 * Information Disclosure in Meta Tags Diagnostic
 *
 * Philosophy: Information disclosure - prevent version leaks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for information disclosure in headers and meta tags.
 */
class Diagnostic_Information_Disclosure_Meta {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Check if generator meta tag reveals WordPress version
		if ( has_action( 'wp_head' ) ) {
			$output = ob_get_clean();
			ob_start();
			
			if ( preg_match( '/<meta name="generator"[^>]*WordPress\s+\d+\.\d+/', $output ) ) {
				return array(
					'id'          => 'information-disclosure-meta',
					'title'       => 'WordPress Version Exposed in Meta Tags',
					'description' => 'WordPress version revealed in meta tags. Attackers know exact version to target. Remove generator meta tag or use generic text.',
					'severity'    => 'medium',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/hide-wordpress-version/',
					'training_link' => 'https://wpshadow.com/training/information-disclosure/',
					'auto_fixable' => false,
					'threat_level' => 55,
				);
			}
			
			ob_end_clean();
		}
		
		return null;
	}
}
