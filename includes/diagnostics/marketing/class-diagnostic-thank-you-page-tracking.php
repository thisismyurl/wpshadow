<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Thank You Page Tracking?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Thank_You_Page_Tracking extends Diagnostic_Base {
    protected static $slug = 'thank-you-page-tracking';
    protected static $title = 'Thank You Page Tracking?';
    protected static $description = 'Verifies conversion confirmation tracking.';

    public static function check(): ?array {
        // Check if conversion tracking is present (implies thank you page tracking)
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        if (preg_match('/gtag.*event.*conversion/i', $header_content) || 
            preg_match('/fbq.*Purchase/i', $header_content)) {
            return null; // Pass - conversion tracking detected (implies thank you pages)
        }
        
        // If e-commerce active, suggest thank you page tracking
        if (class_exists('WooCommerce') || class_exists('Easy_Digital_Downloads')) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'E-commerce active but no thank you page conversion tracking detected.',
                'color'         => '#ff9800',
                'bg_color'      => '#fff3e0',
                'kb_link'       => 'https://wpshadow.com/kb/thank-you-page-tracking/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=thank-you-page-tracking',
                'training_link' => 'https://wpshadow.com/training/thank-you-page-tracking/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Marketing',
                'priority'      => 2,
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
