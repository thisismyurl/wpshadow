<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: PHP Version Outdated (SERVER-004)
 * 
 * Detects PHP <8.0.
 * Philosophy: Show value (#9) with speed and security improvements.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Php_Version_Outdated extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check if PHP version is outdated
        $php_version = phpversion();
        
        // Parse version
        $version_parts = explode('.', $php_version);
        $major = (int)$version_parts[0];
        $minor = (int)$version_parts[1];
        
        // PHP 8.1+ is recommended (8.2+ preferred)
        if ($major < 8 || ($major === 8 && $minor < 1)) {
            return array(
                'id' => 'php-version-outdated',
                'title' => sprintf(__('PHP Version %s - Upgrade Recommended', 'wpshadow'), $php_version),
                'description' => __('PHP 8.2+ is recommended for security, performance, and compatibility. Contact your hosting provider about upgrading.', 'wpshadow'),
                'severity' => 'high',
                'category' => 'code-quality',
                'kb_link' => 'https://wpshadow.com/kb/php-version-upgrade/',
                'training_link' => 'https://wpshadow.com/training/php-compatibility/',
                'auto_fixable' => false,
                'threat_level' => 80,
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
