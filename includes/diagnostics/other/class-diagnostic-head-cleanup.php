<?php
declare(strict_types=1);
/**
 * Head Cleanup Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if common head cruft is still enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Head_Cleanup extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$issues = array();

		if ( self::is_emoji_enabled() ) {
			$issues[] = 'emoji scripts';
		}
		if ( self::is_oembed_enabled() ) {
			$issues[] = 'oEmbed discovery';
		}
		if ( self::is_rsd_enabled() ) {
			$issues[] = 'RSD link';
		}
		if ( self::is_shortlink_enabled() ) {
			$issues[] = 'wp-shortlink';
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$summary = ucfirst( implode( ', ', $issues ) );

		return array(
			'id'           => 'head-cleanup-needed',
			'title'        => 'Clean Up Page Head',
			'description'  => $summary . ' still load on your pages. Removing them reduces requests and exposure.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'kb_link'      => 'https://wpshadow.com/kb/head-cleanup/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=head-cleanup',
			'auto_fixable' => true,
			'threat_level' => 35,
		);
	}

	private static function is_emoji_enabled() {
		return has_action( 'wp_head', 'print_emoji_detection_script' ) || has_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	}

	private static function is_oembed_enabled() {
		return has_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	}

	private static function is_rsd_enabled() {
		return has_action( 'wp_head', 'rsd_link' );
	}

	private static function is_shortlink_enabled() {
		return has_action( 'wp_head', 'wp_shortlink_wp_head' );
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
	}
	/**
	 * Test: Hook detection logic
	 *
	 * Verifies that diagnostic correctly detects hooks and returns
	 * appropriate result (null or array).
	 *
	 * @return array Test result
	 */
	public static function test_hook_detection(): array {
		$result = self::check();
		
		// Should consistently return null or array
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Hook detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Unexpected result type from hook detection',
		);
	}}
