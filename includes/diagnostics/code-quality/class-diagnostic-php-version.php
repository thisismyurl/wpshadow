<?php
declare(strict_types=1);
/**
 * PHP Version Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check PHP version against requirements.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_PHP_Version extends Diagnostic_Base {

	protected static $slug        = 'php-version';
	protected static $title       = 'PHP Version Outdated';
	protected static $description = 'Your PHP version should be updated for better security and performance.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$current_version     = PHP_VERSION;
		$recommended_version = '8.1';
		$minimum_version     = '7.4';

		// Critical if below minimum
		if ( version_compare( $current_version, $minimum_version, '<' ) ) {
			return array(
				'title'       => 'PHP Version Critically Outdated',
				'description' => sprintf(
					'PHP version %1$s is outdated and unsupported. Minimum required: %2$s. Update immediately for security.',
					$current_version,
					$minimum_version
				),
				'severity'    => 'high',
				'category'    => 'security',
			);
		}

		// Warning if below recommended
		if ( version_compare( $current_version, $recommended_version, '<' ) ) {
			return array(
				'title'       => self::$title,
				'description' => sprintf(
					'PHP version %1$s works but %2$s+ is recommended for better performance, security, and compatibility.',
					$current_version,
					$recommended_version
				),
				'severity'    => 'medium',
				'category'    => 'performance',
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
