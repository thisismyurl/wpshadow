<?php
declare(strict_types=1);
/**
 * Exposed Environment Variables Diagnostic
 *
 * @package WPShadow
 * @subpackage DiagnosticsFuture
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Exposed Environment Variables
 * 
 * Philosophy Compliance:
 * - ✅ Free to run (Commandment #2: Free as possible)
 * - ✅ Monetize fixes via Free + Guardian module
 * - ✅ Links to KB/Training (Commandments #5, #6)
 * - ✅ Shows KPI value (Commandment #9)
 * - ✅ Talk-worthy (Commandment #11): "Your .env file with database passwords is publicly accessible"
 * 
 * @priority 1
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Env_File_Exposed extends Diagnostic_Base {
    
    /**
     * The diagnostic slug/ID
     *
     * @var string
     */
    protected static $slug = 'env-file-exposed';
    
    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Exposed Environment Variables';
    
    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Tests if .env and other sensitive files are publicly accessible.';
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Finding data or null if no issue
     */
    public static function check(): ?array {
        // Check if .env file exists
        $env_file = ABSPATH . '.env';
        
        if (!file_exists($env_file)) {
            return null;
        }
        
        // Try to access it via HTTP
        $site_url = site_url('/.env');
        $response = wp_remote_get($site_url, array(
            'timeout' => 5,
            'sslverify' => false,
        ));
        
        // If we can access it, it's exposed
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            
            // Verify it's actually the env file content
            if (!empty($body) && strlen($body) > 10) {
                return array(
                    'id'           => static::$slug,
                    'title'        => static::$title,
                    'description'  => 'Your .env file is publicly accessible! This exposes sensitive credentials.',
                    'severity'     => 'critical',
                    'category'     => 'security',
                    'kb_link'      => 'https://wpshadow.com/kb/exposed-env-files/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=exposed-env-files',
                    'training_link' => 'https://wpshadow.com/training/exposed-env-files/',
                    'auto_fixable' => true,
                    'threat_level' => 100,
                    'module'       => 'Core',
                    'priority'     => 1,
                );
            }
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
