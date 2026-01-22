<?php
declare(strict_types=1);
/**
 * Low Publication Frequency Diagnostic
 *
 * Philosophy: SEO momentum - consistent publishing signals active site
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check publication frequency.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Low_Publication_Frequency extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$three_months_ago = date( 'Y-m-d', strtotime( '-3 months' ) );
		
		$recent_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_status = 'publish' 
				AND post_type = 'post' 
				AND post_date > %s",
				$three_months_ago
			)
		);
		
		if ( $recent_posts < 4 ) {
			return array(
				'id'          => 'seo-low-publication-frequency',
				'title'       => 'Low Publication Frequency',
				'description' => sprintf( 'Only %d posts in last 3 months. Regular publishing signals active site. Aim for 1-2 quality posts per week. Consistency matters more than volume.', $recent_posts ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/improve-publishing-frequency/',
				'training_link' => 'https://wpshadow.com/training/content-calendar/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
