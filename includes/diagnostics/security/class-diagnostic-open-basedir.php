<?php
declare(strict_types=1);
/**
 * Open Basedir Restriction Diagnostic
 *
 * Philosophy: Shared hosting security - isolate users
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if open_basedir is configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Open_Basedir extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$open_basedir = ini_get( 'open_basedir' );
		
		// Check if on shared hosting (heuristic)
		$is_shared = false;
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '';
		
		if ( strpos( $server_software, 'cPanel' ) !== false || 
		     strpos( ABSPATH, '/home/' ) === 0 ) {
			$is_shared = true;
		}
		
		if ( $is_shared && empty( $open_basedir ) ) {
			return array(
				'id'          => 'open-basedir',
				'title'       => 'open_basedir Not Configured on Shared Hosting',
				'description' => 'You appear to be on shared hosting without open_basedir restriction. This allows your PHP scripts to read other users\' files on the same server. Contact your host to enable open_basedir or move to isolated hosting.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/configure-open-basedir/',
				'training_link' => 'https://wpshadow.com/training/hosting-security/',
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
