<?php
declare(strict_types=1);
/**
 * Featured Image Missing Diagnostic
 *
 * Philosophy: Featured images improve social sharing
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Featured_Image_Missing extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $total = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'");
        $with_thumb = (int) $wpdb->get_var("SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_status = 'publish' AND p.post_type = 'post' AND pm.meta_key = '_thumbnail_id'");
        $missing = $total - $with_thumb;
        if ($missing > 5 && $missing > ($total * 0.2)) {
            return [
                'id' => 'seo-featured-image-missing',
                'title' => 'Posts Missing Featured Images',
                'description' => sprintf('%d posts without featured images. Add images for better social sharing and visual appeal.', $missing),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/featured-images/',
                'training_link' => 'https://wpshadow.com/training/image-seo/',
                'auto_fixable' => false,
                'threat_level' => 25,
            ];
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
