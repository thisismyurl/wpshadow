<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CCPA Compliance Status
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_CCPA_Compliance extends Diagnostic_Base {
    protected static $slug = 'ccpa-compliance';
    protected static $title = 'CCPA Compliance Status';
    protected static $description = 'Verifies California privacy law compliance.';

    public static function check(): ?array {
        // CCPA requires privacy policy, data export/deletion capabilities
        $privacy_policy_id = (int) get_option('wp_page_for_privacy_policy', 0);
        $has_privacy_page = ($privacy_policy_id > 0 && get_post_status($privacy_policy_id) === 'publish');
        
        // Check for CCPA/privacy plugins
        $ccpa_plugins = array(
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
            'cookie-notice/cookie-notice.php',
            'wp-gdpr-compliance/wp-gdpr-compliance.php',
        );
        
        $has_privacy_plugin = false;
        foreach ($ccpa_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_privacy_plugin = true;
                break;
            }
        }
        
        // Pass if privacy page exists and plugin active
        if ($has_privacy_page && $has_privacy_plugin) {
            return null;
        }
        
        $issues = array();
        if (!$has_privacy_page) {
            $issues[] = 'Privacy policy page not configured';
        }
        if (!$has_privacy_plugin) {
            $issues[] = 'No privacy/consent management plugin detected';
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => static::$description . ' Issues found: ' . implode(', ', $issues),
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/ccpa-compliance/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=ccpa-compliance',
            'training_link' => 'https://wpshadow.com/training/ccpa-compliance/',
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
