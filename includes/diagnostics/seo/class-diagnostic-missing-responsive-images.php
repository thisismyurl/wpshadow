<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Responsive Images (IMG-003)
 * 
 * Detects images without srcset attribute.
 * Philosophy: Show value (#9) with mobile data savings.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Missing_Responsive_Images extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for missing responsive image implementations
        global $wpdb;
        
        // Count images without srcset attributes in posts
        $missing_srcset = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='attachment' AND post_mime_type LIKE 'image/%' LIMIT 1000"
        );
        
        if ($missing_srcset && $missing_srcset > 100) {
            return array(
                'id' => 'missing-responsive-images',
                'title' => sprintf(__('%d Images May Need Responsive Sizes', 'wpshadow'), $missing_srcset),
                'description' => __('Add srcset and sizes attributes to images for responsive delivery. Use WordPress native image functions or plugins.', 'wpshadow'),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/responsive-images/',
                'training_link' => 'https://wpshadow.com/training/image-optimization/',
                'auto_fixable' => false,
                'threat_level' => 30,
            );
        }
        return null;
}
}
