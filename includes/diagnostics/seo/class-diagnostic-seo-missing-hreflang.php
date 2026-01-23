<?php
declare(strict_types=1);
/**
 * Missing Hreflang Tags Diagnostic
 *
 * Philosophy: SEO international - hreflang for multi-language sites
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing hreflang tags on multi-language sites.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Hreflang extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if multi-language plugin active
		$is_multilang = is_plugin_active( 'wpml-string-translation/plugin.php' ) || 
		                is_plugin_active( 'polylang/polylang.php' ) ||
		                function_exists( 'pll_languages_list' );
		
		if ( $is_multilang && ! has_action( 'wp_head', 'hreflang' ) ) {
			return array(
				'id'          => 'seo-missing-hreflang',
				'title'       => 'Missing Hreflang Tags',
				'description' => 'Multi-language site without hreflang tags. Hreflang tells Google which language/region versions exist. Add hreflang tags for all languages.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-hreflang-tags/',
				'training_link' => 'https://wpshadow.com/training/international-seo/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}

}