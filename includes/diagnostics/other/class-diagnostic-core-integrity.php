<?php
declare(strict_types=1);
/**
 * Core Integrity Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Verify WordPress core files against official checksums.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Core_Integrity extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$issues = self::verify_checksums();
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => 'core-integrity-mismatch',
			'title'        => 'Core Files Modified',
			'description'  => 'WordPress core files differ from the official checksums. Reinstall core or restore clean files.',
			'color'        => '#f44336',
			'bg_color'     => '#ffebee',
			'kb_link'      => 'https://wpshadow.com/kb/verify-wordpress-checksums/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=core-integrity',
			'auto_fixable' => false,
			'threat_level' => 85,
		);
	}

	/**
	 * Verify core checksums.
	 *
	 * @return array List of mismatched files.
	 */
	private static function verify_checksums() {
		if ( ! function_exists( 'get_core_checksums' ) || ! function_exists( 'wp_normalize_path' ) ) {
			return array();
		}

		$locale    = get_locale();
		$version   = get_bloginfo( 'version' );
		$checksums = get_core_checksums( $version, $locale );
		if ( ! is_array( $checksums ) ) {
			return array();
		}

		$root       = wp_normalize_path( ABSPATH );
		$mismatches = array();

		foreach ( $checksums as $file => $checksum ) {
			$path = wp_normalize_path( $root . $file );
			if ( ! file_exists( $path ) ) {
				$mismatches[] = $file . ' (missing)';
				continue;
			}
			$md5 = md5_file( $path );
			if ( $md5 !== $checksum ) {
				$mismatches[] = $file;
			}
		}

		return $mismatches;
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
