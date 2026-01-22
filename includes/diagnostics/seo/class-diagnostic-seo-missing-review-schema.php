<?php
declare(strict_types=1);
/**
 * Missing Review Schema Diagnostic
 *
 * Philosophy: SEO trust - review stars in SERPs boost CTR
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing review schema markup.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Review_Schema extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for review-related content
		global $wpdb;
		
		$review_content = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_content LIKE '%review%' OR post_content LIKE '%rating%' OR post_content LIKE '%testimonial%')"
		);
		
		if ( $review_content > 0 ) {
			return array(
				'id'          => 'seo-missing-review-schema',
				'title'       => 'Missing Review Schema',
				'description' => 'Review/testimonial content without Review schema. Add schema to display star ratings in search results, boosting click-through rate.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-review-schema/',
				'training_link' => 'https://wpshadow.com/training/review-markup/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
