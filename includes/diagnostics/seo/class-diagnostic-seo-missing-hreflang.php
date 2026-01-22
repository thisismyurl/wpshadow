<?php
declare(strict_types=1);
/**
 * Missing Hreflang Tags Diagnostic
 *
 * Philosophy: SEO international - hreflang for multi-language sites
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing hreflang tags on multi-language sites.
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
