<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Can You Still Log In?
 * 
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Login_Works extends Diagnostic_Base {
    protected static $slug = 'login-works';
    protected static $title = 'Can You Still Log In?';
    protected static $description = 'Tests admin login functionality.';

    public static function check(): ?array {
        // If we're logged in and can run this diagnostic, login works!
        // This is more of a sanity check diagnostic
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'Cannot verify - you are not logged in.',
                'severity'      => 'info',
                'category'      => 'security',
                'kb_link'       => 'https://wpshadow.com/kb/login-works/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=login-works',
                'training_link' => 'https://wpshadow.com/training/login-works/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Security',
                'priority'      => 1,
            );
        }
        
        // Check if wp-login.php is accessible
        $login_url = wp_login_url();
        $response = wp_remote_get($login_url, array('timeout' => 10));
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return array(
                'id'            => static::$slug,
                'title'         => 'Login Page May Be Blocked',
                'description'   => 'wp-login.php appears to be inaccessible or blocked.',
                'severity'      => 'high',
                'category'      => 'security',
                'kb_link'       => 'https://wpshadow.com/kb/login-works/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=login-works',
                'training_link' => 'https://wpshadow.com/training/login-works/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Security',
                'priority'      => 1,
            );
        }
        
        // Login works!
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
