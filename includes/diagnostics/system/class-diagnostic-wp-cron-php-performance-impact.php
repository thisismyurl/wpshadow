<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: wp-cron.php Performance Impact (CORE-005)
 * 
 * Checks if wp-cron runs on every page load.
 * Philosophy: Show value (#9) with external cron benefits.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Wp_Cron_Php_Performance_Impact extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$inline_cron = !defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON;
		$alternate_cron = defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON;

		if ($inline_cron && !$alternate_cron) {
			return array(
				'id' => 'wp-cron-php-performance-impact',
				'title' => __('wp-cron.php runs on every page load', 'wpshadow'),
				'description' => __('Inline wp-cron can slow page responses. Disable wp-cron and trigger it with a real cron job or hosting scheduler.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'system',
				'kb_link' => 'https://wpshadow.com/kb/disable-wp-cron/',
				'training_link' => 'https://wpshadow.com/training/wp-cron-optimization/',
				'auto_fixable' => false,
				'threat_level' => 45,
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
