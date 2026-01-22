<?php declare(strict_types=1);
/**
 * SEO Spam Injection Detection Diagnostic
 *
 * Philosophy: Content security - detect SEO spam/cloaking
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for SEO spam and cloaking.
 */
class Diagnostic_SEO_Spam_Injection {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		// Check for hidden text (common SEO spam technique)
		$results = $wpdb->get_results(
			"SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%display:none%' OR post_content LIKE '%visibility:hidden%' LIMIT 5"
		);
		
		if ( ! empty( $results ) ) {
			return array(
				'id'          => 'seo-spam-injection',
				'title'       => 'SEO Spam/Hidden Content Detected',
				'description' => sprintf(
					'Found %d posts with hidden content (CSS display:none or visibility:hidden). This is SEO spam attempting to inject keywords for search manipulation.',
					count( $results )
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/remove-seo-spam/',
				'training_link' => 'https://wpshadow.com/training/seo-spam-removal/',
				'auto_fixable' => false,
				'threat_level' => 65,
			);
		}
		
		return null;
	}
}
