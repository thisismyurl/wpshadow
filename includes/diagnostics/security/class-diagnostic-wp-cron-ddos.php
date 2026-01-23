<?php
declare(strict_types=1);
/**
 * WP-Cron DDOS Amplification Diagnostic
 *
 * Philosophy: Performance security - prevent cron flooding
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if wp-cron.php is publicly accessible.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_WP_Cron_DDOS extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if WP_CRON is disabled (recommended)
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			return null; // Properly configured
		}
		
		// Test if wp-cron.php is accessible
		$cron_url = site_url( 'wp-cron.php' );
		$response = wp_remote_post( $cron_url, array(
			'timeout' => 5,
			'blocking' => true,
			'sslverify' => false,
		) );
		
		if ( is_wp_error( $response ) ) {
			return null; // Can't access (maybe already blocked)
		}
		
		$status = wp_remote_retrieve_response_code( $response );
		
		if ( $status === 200 ) {
			return array(
				'id'          => 'wp-cron-ddos',
				'title'       => 'WP-Cron DDOS Amplification Risk',
				'description' => 'wp-cron.php is publicly accessible and processes on every request. Attackers can flood this endpoint to amplify DDOS attacks and overload your server. Define DISABLE_WP_CRON and use system cron.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-wp-cron/',
				'training_link' => 'https://wpshadow.com/training/cron-security/',
				'auto_fixable' => false,
				'threat_level' => 65,
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
