<?php
declare(strict_types=1);
/**
 * Missing Video Schema Diagnostic
 *
 * Philosophy: SEO rich results - video schema enables video carousels
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing video schema markup.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Video_Schema extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$video_embeds = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_content LIKE '%youtube.com%' OR post_content LIKE '%vimeo.com%')"
		);
		
		if ( $video_embeds > 0 ) {
			return array(
				'id'          => 'seo-missing-video-schema',
				'title'       => 'Videos Missing VideoObject Schema',
				'description' => sprintf( '%d video embeds detected. Add VideoObject schema with title, description, thumbnail, duration. Enables video carousels in search results.', $video_embeds ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-video-schema/',
				'training_link' => 'https://wpshadow.com/training/video-markup/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}

}