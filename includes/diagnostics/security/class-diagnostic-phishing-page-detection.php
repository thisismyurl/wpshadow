<?php
declare(strict_types=1);
/**
 * Phishing Page Detection Diagnostic
 *
 * Philosophy: Content security - detect phishing kits
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for phishing page indicators.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Phishing_Page_Detection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Scan post content for phishing patterns
		$results = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts} WHERE post_content LIKE '%<form%action%' AND post_status = 'publish' LIMIT 5"
		);
		
		if ( ! empty( $results ) ) {
			foreach ( $results as $post ) {
				$content = get_post_field( 'post_content', $post->ID );
				
				// Look for suspicious form patterns
				if ( preg_match( '/password|credit.?card|ssn|account.?number/i', $content ) ) {
					return array(
						'id'          => 'phishing-page-detection',
						'title'       => 'Possible Phishing Page Detected',
						'description' => sprintf(
							'Post "%s" contains forms requesting sensitive information (passwords, credit cards). This may be a phishing kit. Review and remove immediately.',
							esc_html( $post->post_title )
						),
						'severity'    => 'critical',
						'category'    => 'security',
						'kb_link'     => 'https://wpshadow.com/kb/remove-phishing-pages/',
						'training_link' => 'https://wpshadow.com/training/phishing-removal/',
						'auto_fixable' => false,
						'threat_level' => 90,
					);
				}
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
