<?php declare(strict_types=1);
/**
 * International Targeting Diagnostic
 *
 * Philosophy: SEO global - target international audiences properly
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for international targeting in GSC.
 */
class Diagnostic_SEO_International_Targeting {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		return array(
			'id'          => 'seo-international-targeting',
			'title'       => 'Configure International Targeting',
			'description' => 'If targeting specific country, set in Search Console: Settings > Country. For multi-country sites, use hreflang tags or ccTLDs (.co.uk, .ca). Helps Google show right version to right users.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/international-targeting/',
			'training_link' => 'https://wpshadow.com/training/global-seo/',
			'auto_fixable' => false,
			'threat_level' => 45,
		);
	}
}
