<?php
declare(strict_types=1);
/**
 * Missing Image Alt Text Diagnostic
 *
 * Philosophy: SEO accessibility - alt text helps rankings
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing image alt text.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Image_Alt_Text extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$images = $wpdb->get_results(
			"SELECT ID FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%' 
			LIMIT 20"
		);
		
		$missing = 0;
		foreach ( $images as $image ) {
			$alt_text = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
			if ( empty( $alt_text ) ) {
				$missing++;
			}
		}
		
		if ( $missing > 0 ) {
			return array(
				'id'          => 'seo-missing-image-alt-text',
				'title'       => 'Images Missing Alt Text',
				'description' => sprintf( '%d images missing alt text. Alt text improves accessibility and SEO. Add descriptive alt text to all images.', $missing ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-image-alt-text/',
				'training_link' => 'https://wpshadow.com/training/image-seo/',
				'auto_fixable' => false,
				'threat_level' => 50,
			);
		}
		
		return null;
	}

}