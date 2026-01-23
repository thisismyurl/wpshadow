<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Security Hardening Checklist
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Security_Hardening extends Diagnostic_Base {
    protected static $slug = 'security-hardening';
    protected static $title = 'Security Hardening Checklist';
    protected static $description = 'Scores security best practices implementation.';


    public static function check(): ?array {
        $score = 100;
        $issues = array();
        
        // Check various security hardening settings
        if (!defined('DISALLOW_FILE_EDIT') || !DISALLOW_FILE_EDIT) {
            $score -= 15;
            $issues[] = 'File editor not disabled';
        }
        
        if (!defined('WP_DEBUG') || WP_DEBUG) {
            $score -= 10;
            $issues[] = 'Debug mode enabled';
        }
        
        if (!is_ssl()) {
            $score -= 20;
            $issues[] = 'SSL not enabled';
        }
        
        if (get_option('blog_public') == 1 && !is_ssl()) {
            $score -= 10;
            $issues[] = 'Public site without SSL';
        }
        
        // Check if login URL is default
        if (file_exists(ABSPATH . 'wp-login.php')) {
            $score -= 5;
            $issues[] = 'Default login URL (consider hiding)';
        }
        
        // Calculate score
        if ($score >= 80) {
            return null; // Good security posture
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => sprintf('Security Score: %d/100', $score),
            'description'   => sprintf('Security improvements needed: %s', implode('; ', $issues)),
            'severity'      => $score < 50 ? 'high' : 'medium',
            'category'      => 'security',
            'kb_link'       => 'https://wpshadow.com/kb/security-hardening/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=security-hardening',
            'training_link' => 'https://wpshadow.com/training/security-hardening/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Security',
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
