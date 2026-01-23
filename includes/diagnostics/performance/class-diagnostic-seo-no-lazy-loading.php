<?php
declare(strict_types=1);
/**
 * No Lazy Loading Diagnostic
 *
 * Philosophy: SEO performance - lazy load improves initial load
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if lazy loading is enabled for images.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_No_Lazy_Loading extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$images = $wpdb->get_results(
			"SELECT post_content FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 5"
		);
		
		$has_lazy = false;
		foreach ( $images as $post ) {
			if ( strpos( $post->post_content, 'loading="lazy"' ) !== false ) {
				$has_lazy = true;
				break;
			}
		}
		
		if ( ! $has_lazy ) {
			return array(
				'id'          => 'seo-no-lazy-loading',
				'title'       => 'Lazy Loading Not Enabled',
				'description' => 'Images not using lazy loading. Lazy loading defers offscreen images, improving initial page load. WordPress 5.5+ supports native lazy loading.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/enable-lazy-loading/',
				'training_link' => 'https://wpshadow.com/training/lazy-loading/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}

}