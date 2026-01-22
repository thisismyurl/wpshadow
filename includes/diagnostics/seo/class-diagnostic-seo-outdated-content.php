<?php
declare(strict_types=1);
/**
 * Outdated Content Diagnostic
 *
 * Philosophy: SEO freshness - Google favors updated content
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for outdated content (not updated in 2+ years).
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Outdated_Content extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$two_years_ago = date( 'Y-m-d', strtotime( '-2 years' ) );
		
		$outdated = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_status = 'publish' 
				AND post_type IN ('post', 'page') 
				AND post_modified < %s",
				$two_years_ago
			)
		);
		
		if ( $outdated > 10 ) {
			return array(
				'id'          => 'seo-outdated-content',
				'title'       => 'Outdated Content Detected',
				'description' => sprintf( '%d pages not updated in 2+ years. Google favors fresh content. Review, update, and republish old content. Add "Last Updated" dates.', $outdated ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/update-old-content/',
				'training_link' => 'https://wpshadow.com/training/content-freshness/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
