<?php declare(strict_types=1);
/**
 * Missing UTM Parameters Diagnostic
 *
 * Philosophy: SEO attribution - track campaign effectiveness
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for UTM parameter usage in campaigns.
 */
class Diagnostic_SEO_Missing_UTM_Parameters {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$utm_links = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_content LIKE '%utm_source%' 
			OR post_content LIKE '%utm_medium%'"
		);
		
		if ( $utm_links === 0 ) {
			return array(
				'id'          => 'seo-missing-utm-parameters',
				'title'       => 'No UTM Parameter Usage',
				'description' => 'No UTM parameters detected in content. UTM tags track campaign performance in Analytics. Tag social, email, and paid links with utm_source, utm_medium, utm_campaign.',
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/use-utm-parameters/',
				'training_link' => 'https://wpshadow.com/training/campaign-tracking/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
