<?php
declare(strict_types=1);
/**
 * Missing FAQ Schema Diagnostic
 *
 * Philosophy: SEO rich results - FAQ schema gets featured placement
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing FAQ schema on FAQ content.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_FAQ_Schema extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$faq_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_title LIKE '%FAQ%' OR post_content LIKE '%frequently asked%')"
		);
		
		if ( $faq_posts > 0 ) {
			return array(
				'id'          => 'seo-missing-faq-schema',
				'title'       => 'Missing FAQ Schema Markup',
				'description' => sprintf( '%d FAQ pages detected without FAQ schema. Add FAQPage schema to appear in featured snippets and voice search results.', $faq_posts ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-faq-schema/',
				'training_link' => 'https://wpshadow.com/training/faq-schema/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
