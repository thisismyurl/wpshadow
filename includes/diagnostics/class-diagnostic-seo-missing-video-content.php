<?php declare(strict_types=1);
/**
 * Missing Video Content Diagnostic
 *
 * Philosophy: SEO engagement - video improves dwell time
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for lack of video content.
 */
class Diagnostic_SEO_Missing_Video_Content {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$video_embeds = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_content LIKE '%youtube.com%' OR post_content LIKE '%vimeo.com%' OR post_content LIKE '%<video%')"
		);
		
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type = 'post'"
		);
		
		if ( $total_posts > 20 && $video_embeds < 5 ) {
			return array(
				'id'          => 'seo-missing-video-content',
				'title'       => 'Limited Video Content',
				'description' => sprintf( 'Only %d videos across %d posts. Video content improves engagement, dwell time, and can rank in video search. Add YouTube videos or screen recordings.', $video_embeds, $total_posts ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-video-content/',
				'training_link' => 'https://wpshadow.com/training/video-seo/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
