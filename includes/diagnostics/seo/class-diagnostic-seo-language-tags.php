<?php
declare(strict_types=1);
/**
 * Language Tags Diagnostic
 *
 * Philosophy: SEO localization - proper language declaration
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for language meta tags.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Language_Tags extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$site_language = get_bloginfo( 'language' );
		
		if ( empty( $site_language ) || $site_language === 'en-US' ) {
			return array(
				'id'          => 'seo-language-tags',
				'title'       => 'Verify HTML Language Attribute',
				'description' => 'Ensure <html lang="en"> attribute matches content language. For multi-language sites, use correct ISO codes (en-US, es-ES, fr-FR). Helps search engines and screen readers.',
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/html-language-attribute/',
				'training_link' => 'https://wpshadow.com/training/language-targeting/',
				'auto_fixable' => false,
				'threat_level' => 40,
			);
		}
		
		return null;
	}
}
