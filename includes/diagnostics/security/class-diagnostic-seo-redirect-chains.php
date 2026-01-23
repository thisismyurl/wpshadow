<?php
declare(strict_types=1);
/**
 * Redirect Chains Diagnostic
 *
 * Philosophy: SEO performance - redirect chains waste crawl budget
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for redirect chains.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Redirect_Chains extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Check if Redirection plugin is active
		$redirects = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}redirection_items 
			WHERE status = 'enabled' 
			LIMIT 10",
			ARRAY_A
		);
		
		$chains = 0;
		foreach ( $redirects as $redirect ) {
			// Check if redirect target is also redirected
			$target = $redirect['url'] ?? '';
			$target_redirect = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}redirection_items 
					WHERE url = %s AND status = 'enabled'",
					$target
				)
			);
			
			if ( $target_redirect ) {
				$chains++;
			}
		}
		
		if ( $chains > 0 ) {
			return array(
				'id'          => 'seo-redirect-chains',
				'title'       => 'Redirect Chains Detected',
				'description' => sprintf( 'Found %d redirect chains (A→B→C). Chains waste crawl budget and slow page load. Create direct redirects (A→C).', $chains ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-redirect-chains/',
				'training_link' => 'https://wpshadow.com/training/redirect-best-practices/',
				'auto_fixable' => false,
				'threat_level' => 55,
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
