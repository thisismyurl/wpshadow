<?php
declare(strict_types=1);
/**
 * Non-HTTPS Content Diagnostic
 *
 * Philosophy: SEO security - HTTPS is ranking factor
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for mixed content (HTTP resources on HTTPS pages).
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Non_HTTPS_Content extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! is_ssl() ) {
			return array(
				'id'          => 'seo-non-https-content',
				'title'       => 'Site Not Using HTTPS',
				'description' => 'Site not using HTTPS. Google favors secure sites in rankings. Install SSL certificate and force HTTPS.',
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/enable-https/',
				'training_link' => 'https://wpshadow.com/training/ssl-setup/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}
		
		// Check for mixed content
		global $wpdb;
		$mixed = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_content LIKE '%http://%'"
		);
		
		if ( $mixed > 0 ) {
			return array(
				'id'          => 'seo-mixed-content',
				'title'       => 'Mixed Content Detected',
				'description' => sprintf( '%d pages contain HTTP resources on HTTPS site. Mixed content triggers browser warnings. Update all HTTP URLs to HTTPS.', $mixed ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-mixed-content/',
				'training_link' => 'https://wpshadow.com/training/https-migration/',
				'auto_fixable' => false,
				'threat_level' => 60,
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
