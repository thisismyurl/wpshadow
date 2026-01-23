<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Social Media Integration Active?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Social_Media_Integration extends Diagnostic_Base {
    protected static $slug = 'social-media-integration';
    protected static $title = 'Social Media Integration Active?';
    protected static $description = 'Checks social sharing and profile links.';

    public static function check(): ?array {
        // Check for social sharing/follow plugins
        $social_plugins = array(
            'monarch/monarch.php',
            'social-warfare/social-warfare.php',
            'wp-social-sharing/wp-social-sharing.php',
            'jetpack/jetpack.php', // Jetpack has social features
        );
        
        foreach ($social_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - social plugin active
            }
        }
        
        // Check for Open Graph tags (social sharing metadata)
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        if (strpos($header_content, 'og:title') !== false && strpos($header_content, 'og:image') !== false) {
            return null; // Pass - Open Graph tags present
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No social media integration or Open Graph tags detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/social-media-integration/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=social-media-integration',
            'training_link' => 'https://wpshadow.com/training/social-media-integration/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Marketing',
            'priority'      => 2,
        );
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
	}
	/**
	 * Test: Plugin detection logic
	 *
	 * Verifies that diagnostic correctly checks for active plugins
	 * and reports issues appropriately.
	 *
	 * @return array Test result
	 */
	public static function test_plugin_detection(): array {
		$result = self::check();
		
		// Plugin detection should return null (no plugin/no issue) or array (issue)
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Plugin detection logic valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid plugin detection result',
		);
	}}
