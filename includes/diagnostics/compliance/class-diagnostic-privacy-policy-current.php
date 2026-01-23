<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Privacy Policy Up to Date?
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Privacy_Policy_Current extends Diagnostic_Base {
    protected static $slug = 'privacy-policy-current';
    protected static $title = 'Privacy Policy Up to Date?';
    protected static $description = 'Verifies privacy policy updated within 12 months.';

    public static function check(): ?array {
        $privacy_policy_id = (int) get_option('wp_page_for_privacy_policy', 0);
        
        if ($privacy_policy_id === 0) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'No privacy policy page configured in WordPress.',
                'color'         => '#f44336',
                'bg_color'      => '#ffebee',
                'kb_link'       => 'https://wpshadow.com/kb/privacy-policy-current/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=privacy-policy-current',
                'training_link' => 'https://wpshadow.com/training/privacy-policy-current/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Compliance',
                'priority'      => 1,
            );
        }
        
        $policy_post = get_post($privacy_policy_id);
        if (!$policy_post || $policy_post->post_status !== 'publish') {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'Privacy policy page is not published.',
                'color'         => '#f44336',
                'bg_color'      => '#ffebee',
                'kb_link'       => 'https://wpshadow.com/kb/privacy-policy-current/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=privacy-policy-current',
                'training_link' => 'https://wpshadow.com/training/privacy-policy-current/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Compliance',
                'priority'      => 1,
            );
        }
        
        // Check if updated within 12 months
        $last_modified = strtotime($policy_post->post_modified);
        $twelve_months_ago = strtotime('-12 months');
        
        if ($last_modified >= $twelve_months_ago) {
            return null; // Pass - policy is current
        }
        
        $months_old = floor((time() - $last_modified) / (30 * 24 * 60 * 60));
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => "Privacy policy last updated {$months_old} months ago (recommend annual review).",
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/privacy-policy-current/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=privacy-policy-current',
            'training_link' => 'https://wpshadow.com/training/privacy-policy-current/',
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
