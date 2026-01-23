<?php
declare(strict_types=1);
/**
 * Session Hijacking Vulnerability Diagnostic
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
 * Session Hijacking Vulnerability
 * 
 * Philosophy Compliance:
 * - ✅ Free to run (Commandment #2: Free as possible)
 * - ✅ Monetize fixes via Free module
 * - ✅ Links to KB/Training (Commandments #5, #6)
 * - ✅ Shows KPI value (Commandment #9)
 * - ✅ Talk-worthy (Commandment #11): "Your login cookies can be stolen over public WiFi"
 * 
 * @priority 2
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Session_Hijacking_Vuln extends Diagnostic_Base {
    
    /**
     * The diagnostic slug/ID
     *
     * @var string
     */
    protected static $slug = 'session-hijacking-vuln';
    
    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Session Hijacking Vulnerability';
    
    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Tests if login cookies are secure and protected from theft.';
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Finding data or null if no issue
     */
    public static function check(): ?array {
        $issues = array();
        
        // Check if cookies are sent over HTTPS only
        if (!defined('FORCE_SSL_ADMIN') || !FORCE_SSL_ADMIN) {
            if (is_ssl()) {
                $issues[] = 'FORCE_SSL_ADMIN not enabled (cookies may be sent over HTTP)';
            }
        }
        
        // Check if session cookies have secure flag
        $cookie_params = session_get_cookie_params();
        if (is_ssl() && (!isset($cookie_params['secure']) || !$cookie_params['secure'])) {
            $issues[] = 'Session cookies missing secure flag';
        }
        
        // Check if HttpOnly flag is set
        if (!isset($cookie_params['httponly']) || !$cookie_params['httponly']) {
            $issues[] = 'Session cookies missing HttpOnly flag (vulnerable to XSS)';
        }
        
        // Check SameSite attribute
        if (!isset($cookie_params['samesite']) || $cookie_params['samesite'] !== 'Strict') {
            $issues[] = 'Session cookies missing SameSite=Strict (vulnerable to CSRF)';
        }
        
        if (empty($issues)) {
            return null;
        }
        
        return array(
            'id'           => static::$slug,
            'title'        => static::$title,
            'description'  => sprintf(
                'Found %d session security issue(s): %s',
                count($issues),
                implode('; ', $issues)
            ),
            'severity'     => 'high',
            'category'     => 'security',
            'kb_link'      => 'https://wpshadow.com/kb/session-hijacking/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=session-hijacking',
            'training_link' => 'https://wpshadow.com/training/session-hijacking/',
            'auto_fixable' => true,
            'threat_level' => 85,
            'module'       => 'Free',
            'priority'     => 2,
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
	}}
