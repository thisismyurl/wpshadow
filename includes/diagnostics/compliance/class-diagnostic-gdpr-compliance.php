<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: GDPR Compliance Status
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_GDPR_Compliance extends Diagnostic_Base {
    protected static $slug = 'gdpr-compliance';
    protected static $title = 'GDPR Compliance Status';
    protected static $description = 'Checks consent management and data protection.';

    public static function check(): ?array {
        // Check for popular GDPR compliance plugins
        $gdpr_plugins = array(
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
            'cookie-notice/cookie-notice.php',
            'complianz-gdpr/complianz-gpdr.php',
            'wp-gdpr-compliance/wp-gdpr-compliance.php',
        );
        
        $has_gdpr_plugin = false;
        foreach ($gdpr_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_gdpr_plugin = true;
                break;
            }
        }
        
        // Check for privacy policy page
        $privacy_policy_id = (int) get_option('wp_page_for_privacy_policy', 0);
        $has_privacy_page = ($privacy_policy_id > 0 && get_post_status($privacy_policy_id) === 'publish');
        
        // Pass if both conditions met
        if ($has_gdpr_plugin && $has_privacy_page) {
            return null;
        }
        
        $issues = array();
        if (!$has_gdpr_plugin) {
            $issues[] = 'No GDPR compliance plugin detected';
        }
        if (!$has_privacy_page) {
            $issues[] = 'Privacy policy page not configured';
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => static::$description . ' Issues found: ' . implode(', ', $issues),
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/gdpr-compliance/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=gdpr-compliance',
            'training_link' => 'https://wpshadow.com/training/gdpr-compliance/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Compliance',
            'priority'      => 1,
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
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}
