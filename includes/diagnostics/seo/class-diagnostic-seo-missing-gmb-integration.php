<?php
declare(strict_types=1);
/**
 * Missing Google My Business Integration Diagnostic
 *
 * Philosophy: SEO local - GMB is essential for local visibility
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for Google My Business integration.
 */
class Diagnostic_SEO_Missing_GMB_Integration extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if GMB widget or links present
		$site_description = get_bloginfo( 'description' );
		$is_local = preg_match( '/(restaurant|store|shop|salon|clinic|office|local)/i', $site_description );
		
		if ( $is_local ) {
			global $wpdb;
			$gmb_links = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_content LIKE '%google.com/maps%' 
				OR post_content LIKE '%business.google.com%'"
			);
			
			if ( $gmb_links === 0 ) {
				return array(
					'id'          => 'seo-missing-gmb-integration',
					'title'       => 'No Google My Business Integration',
					'description' => 'Local business without Google My Business integration. Claim and verify your GMB listing, embed map on contact page, link to GMB profile.',
					'severity'    => 'high',
					'category'    => 'seo',
					'kb_link'     => 'https://wpshadow.com/kb/integrate-google-my-business/',
					'training_link' => 'https://wpshadow.com/training/gmb-optimization/',
					'auto_fixable' => false,
					'threat_level' => 70,
				);
			}
		}
		
		return null;
	}
}
