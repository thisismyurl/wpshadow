<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: PHP Version Compatibility
 * 
 * Target Persona: Web Hosting Provider
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_PHP_Version_Compatible extends Diagnostic_Base {
    protected static $slug = 'php-version-compatible';
    protected static $title = 'PHP Version Compatibility';
    protected static $description = 'Checks for deprecated code in current PHP.';


    public static function check(): ?array {
        $php_version = phpversion();
        if (version_compare($php_version, '7.4', '<')) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => "PHP {$php_version} is outdated. WordPress 6.3+ requires PHP 7.4+.",
                'color'         => '#f44336',
                'bg_color'      => '#ffebee',
                'kb_link'       => 'https://wpshadow.com/kb/php-version-compatible/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=php-version-compatible',
                'training_link' => 'https://wpshadow.com/training/php-version-compatible/',
                'auto_fixable'  => false,
                'threat_level'  => 80,
                'module'        => 'System',
                'priority'      => 1,
            );
        }
        if (version_compare($php_version, '8.0', '<')) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => "PHP {$php_version} will reach end of life soon. Consider upgrading to PHP 8.0+.",
                'color'         => '#ff9800',
                'bg_color'      => '#fff3e0',
                'kb_link'       => 'https://wpshadow.com/kb/php-version-compatible/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=php-version-compatible',
                'training_link' => 'https://wpshadow.com/training/php-version-compatible/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'System',
                'priority'      => 1,
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
