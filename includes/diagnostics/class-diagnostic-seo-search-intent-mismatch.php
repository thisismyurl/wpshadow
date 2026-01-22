<?php declare(strict_types=1);
/**
 * Search Intent Mismatch Diagnostic
 *
 * Philosophy: SEO relevance - match content to search intent
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if content matches likely search intent.
 */
class Diagnostic_SEO_Search_Intent_Mismatch {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		return array(
			'id'          => 'seo-search-intent-mismatch',
			'title'       => 'Review Search Intent Alignment',
			'description' => 'Ensure content matches search intent: Informational ("what is"), Navigational ("brand name"), Transactional ("buy"), Commercial ("best"). Search Google for target keyword and analyze top 10 results.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/match-search-intent/',
			'training_link' => 'https://wpshadow.com/training/search-intent/',
			'auto_fixable' => false,
			'threat_level' => 55,
		);
	}
}
