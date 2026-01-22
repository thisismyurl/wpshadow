<?php
declare(strict_types=1);
/**
 * Related Posts Implementation Diagnostic
 *
 * Philosophy: SEO engagement - related posts improve session duration
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for related posts.
 */
class Diagnostic_SEO_Related_Posts extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if related posts feature exists
		$has_related = has_action( 'the_content', 'related_posts' ) || 
		               function_exists( 'yarpp_related' ) ||
		               function_exists( 'wp_related_posts' );
		
		if ( ! $has_related ) {
			return array(
				'id'          => 'seo-related-posts',
				'title'       => 'Add Related Posts',
				'description' => 'No related posts detected. Related posts at article end keep visitors engaged, reduce bounce rate, improve session duration. Use Yet Another Related Posts Plugin (YARPP).',
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-related-posts/',
				'training_link' => 'https://wpshadow.com/training/content-discovery/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
