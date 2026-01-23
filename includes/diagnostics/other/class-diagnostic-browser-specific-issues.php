<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Browser-Specific Performance Issues (RUM-004)
 *
 * Identifies performance problems affecting specific browsers.
 * Philosophy: Educate (#5) - Fix browser compatibility issues.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Browser_Specific_Issues extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$issue_count      = (int) get_transient( 'wpshadow_browser_issue_count' );
		$affected_browser = get_transient( 'wpshadow_most_affected_browser' );

		if ( $issue_count > 0 ) {
			return array(
				'id'               => 'browser-specific-issues',
				'title'            => sprintf( __( 'Browser-specific issues detected (%d)', 'wpshadow' ), $issue_count ),
				'description'      => __( 'Certain browsers are experiencing degraded performance or compatibility issues. Test affected browsers and apply targeted fixes.', 'wpshadow' ),
				'severity'         => 'medium',
				'category'         => 'other',
				'kb_link'          => 'https://wpshadow.com/kb/browser-specific-issues/',
				'training_link'    => 'https://wpshadow.com/training/cross-browser-performance/',
				'auto_fixable'     => false,
				'threat_level'     => 45,
				'affected_browser' => $affected_browser,
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
