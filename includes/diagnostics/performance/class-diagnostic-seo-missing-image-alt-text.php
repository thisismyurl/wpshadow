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

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}
