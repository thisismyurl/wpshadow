<?php
declare(strict_types=1);
/**
 * Missing Last Updated Date Diagnostic
 *
 * Philosophy: SEO transparency - show content freshness
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if last updated dates are displayed.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Last_Updated_Date extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT post_content FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 10"
		);
		
		$has_updated_date = false;
		foreach ( $posts as $post ) {
			if ( preg_match( '/last\s+updated|updated\s+on/i', $post->post_content ) ) {
				$has_updated_date = true;
				break;
			}
		}
		
		if ( ! $has_updated_date ) {
			return array(
				'id'          => 'seo-missing-last-updated-date',
				'title'       => 'Last Updated Dates Not Displayed',
				'description' => 'Posts don\'t display last updated dates. Showing update dates signals content freshness to readers and search engines. Add "Last Updated" to post templates.',
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-last-updated-dates/',
				'training_link' => 'https://wpshadow.com/training/content-timestamps/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}

}