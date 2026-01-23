<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Is Site Currently Down?
 * 
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Site_Down extends Diagnostic_Base {
    protected static $slug = 'site-down';
    protected static $title = 'Is Site Currently Down?';
    protected static $description = 'External check to verify site is reachable.';

    public static function check(): ?array {
        $home_url = home_url();
        $response = wp_remote_get($home_url, array(
            'timeout' => 15,
            'sslverify' => false,
        ));
        
        if (is_wp_error($response)) {
            return array(
                'id'            => static::$slug,
                'title'         => __('Site is currently down', 'wpshadow'),
                'description'   => sprintf(
                    __('External check failed: %s. Visitors cannot access your site.', 'wpshadow'),
                    $response->get_error_message()
                ),
                'severity'      => 'critical',
                'category'      => 'monitoring',
                'kb_link'       => 'https://wpshadow.com/kb/site-down/',
                'training_link' => 'https://wpshadow.com/training/site-down/',
                'auto_fixable'  => false,
                'threat_level'  => 100,
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        if ($code >= 500) {
            return array(
                'id'            => static::$slug,
                'title'         => sprintf(__('Site returns server error (HTTP %d)', 'wpshadow'), $code),
                'description'   => __('Your server is experiencing errors. Visitors may see error pages.', 'wpshadow'),
                'severity'      => 'critical',
                'category'      => 'monitoring',
                'kb_link'       => 'https://wpshadow.com/kb/site-down/',
                'training_link' => 'https://wpshadow.com/training/site-down/',
                'auto_fixable'  => false,
                'threat_level'  => 95,
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
