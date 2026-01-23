<?php
declare(strict_types=1);
/**
 * No GZIP Compression Diagnostic
 *
 * Philosophy: SEO performance - GZIP reduces bandwidth by 70%
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if GZIP compression is enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_No_GZIP_Compression extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array( 
			'timeout' => 5,
			'headers' => array( 'Accept-Encoding' => 'gzip' )
		) );
		
		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
			$encoding = $headers['content-encoding'] ?? '';
			
			if ( strpos( $encoding, 'gzip' ) === false ) {
				return array(
					'id'          => 'seo-no-gzip-compression',
					'title'       => 'GZIP Compression Not Enabled',
					'description' => 'GZIP compression not detected. GZIP reduces file sizes by 50-70%, improving page speed. Enable via .htaccess or hosting control panel.',
					'severity'    => 'high',
					'category'    => 'seo',
					'kb_link'     => 'https://wpshadow.com/kb/enable-gzip-compression/',
					'training_link' => 'https://wpshadow.com/training/compression/',
					'auto_fixable' => false,
					'threat_level' => 65,
				);
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
