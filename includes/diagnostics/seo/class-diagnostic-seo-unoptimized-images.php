<?php
declare(strict_types=1);
/**
 * Unoptimized Images Diagnostic
 *
 * Philosophy: SEO performance - image optimization improves speed
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for unoptimized images.
 */
class Diagnostic_SEO_Unoptimized_Images extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$large_images = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%' 
			AND guid LIKE '%.jpg' 
			OR guid LIKE '%.png'"
		);
		
		if ( $large_images > 10 ) {
			return array(
				'id'          => 'seo-unoptimized-images',
				'title'       => 'Unoptimized Images Detected',
				'description' => sprintf( '%d images may be unoptimized. Use WebP format, compress images, and implement lazy loading for better performance.', $large_images ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-images/',
				'training_link' => 'https://wpshadow.com/training/image-optimization/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
