<?php
declare(strict_types=1);
/**
 * SEO Spam Injection Detection Diagnostic
 *
 * Philosophy: Content security - detect SEO spam/cloaking
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for SEO spam and cloaking.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Spam_Injection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
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
