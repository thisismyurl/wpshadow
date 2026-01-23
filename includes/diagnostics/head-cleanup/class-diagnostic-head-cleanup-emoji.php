<?php
declare(strict_types=1);
/**
 * Head Cleanup - Emoji Scripts Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if emoji detection scripts are enabled.
 *
 * Family: head-cleanup
 * Related: head-cleanup-oembed, head-cleanup-rsd, head-cleanup-shortlink
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Head_Cleanup_Emoji extends Diagnostic_Base {

	protected static $slug         = 'head-cleanup-emoji';
	protected static $title        = 'Emoji Detection Scripts';
	protected static $description  = 'Checks if WordPress emoji detection scripts are enabled and can be removed.';
	protected static $family       = 'head-cleanup';
	protected static $family_label = 'Head Cleanup Tasks';

	public static function check(): ?array {
		if ( ! self::is_emoji_enabled() ) {
			return null;
		}

		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Emoji detection scripts load on every page but are rarely needed. Removing them reduces requests and improves performance.', 'wpshadow' ),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 15,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if emoji scripts are enabled
	 *
	 * @return bool
	 */
	private static function is_emoji_enabled(): bool {
		return has_action( 'wp_head', 'print_emoji_detection_script' ) !== false || has_action( 'admin_print_scripts', 'print_emoji_detection_script' ) !== false;
	}

	/**
	 * Test: Emoji scripts disabled (clean state)
	 *
	 * @return array
	 */
	public static function test_emoji_disabled(): array {
		// Setup: Remove emoji hooks if they exist
		remove_action( 'wp_head', 'print_emoji_detection_script' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

		// Execute
		$result = self::check();

		// Verify
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => 'Correctly returned null when emoji disabled',
			);
		}

		return array(
			'passed'  => false,
			'message' => 'Expected null for disabled emoji, got: ' . wp_json_encode( $result ),
		);
	}

	/**
	 * Test: Emoji scripts enabled in wp_head
	 *
	 * @return array
	 */
	public static function test_emoji_enabled_wp_head(): array {
		// Setup: Ensure no emoji hooks first
		remove_action( 'wp_head', 'print_emoji_detection_script' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		
		// Add the emoji hook (typical WordPress setup)
		add_action( 'wp_head', 'print_emoji_detection_script', 7 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'print_emoji_detection_script' );

		// Verify
		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array for enabled emoji, got: ' . gettype( $result ),
			);
		}

		// Validate required fields
		if ( ! isset( $result['id'] ) || $result['id'] !== self::$slug ) {
			return array(
				'passed'  => false,
				'message' => 'Missing or incorrect id field',
			);
		}

		if ( ! isset( $result['category'] ) || $result['category'] !== 'performance' ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect category, expected performance',
			);
		}

		if ( ! isset( $result['severity'] ) || $result['severity'] !== 'low' ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect severity, expected low',
			);
		}

		if ( ! isset( $result['threat_level'] ) || 15 !== $result['threat_level'] ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect threat_level, expected 15',
			);
		}

		if ( ! isset( $result['auto_fixable'] ) || true !== $result['auto_fixable'] ) {
			return array(
				'passed'  => false,
				'message' => 'Expected auto_fixable to be true',
			);
		}

		if ( ! isset( $result['family'] ) || 'head-cleanup' !== $result['family'] ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect family, expected head-cleanup',
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Correctly detected emoji enabled in wp_head and returned proper array',
		);
	}

	/**
	 * Test: Emoji scripts enabled in admin_print_scripts
	 *
	 * @return array
	 */
	public static function test_emoji_enabled_admin_scripts(): array {
		// Setup: Ensure no emoji hooks first
		remove_action( 'wp_head', 'print_emoji_detection_script' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		
		// Add the emoji hook to admin
		add_action( 'admin_print_scripts', 'print_emoji_detection_script', 7 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

		// Verify
		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array for enabled emoji in admin, got: ' . gettype( $result ),
			);
		}

		if ( ! isset( $result['id'] ) || $result['id'] !== self::$slug ) {
			return array(
				'passed'  => false,
				'message' => 'Missing or incorrect id field in admin detection',
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Correctly detected emoji enabled in admin_print_scripts',
		);
	}

	/**
	 * Test: Emoji enabled in both hooks (comprehensive detection)
	 *
	 * @return array
	 */
	public static function test_emoji_enabled_both_hooks(): array {
		// Setup: Ensure no emoji hooks first
		remove_action( 'wp_head', 'print_emoji_detection_script' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		
		// Add emoji hooks to both locations (real WordPress behavior)
		add_action( 'wp_head', 'print_emoji_detection_script', 7 );
		add_action( 'admin_print_scripts', 'print_emoji_detection_script', 7 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'print_emoji_detection_script' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

		// Verify
		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array when emoji enabled in both hooks',
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Correctly detected emoji in both wp_head and admin_print_scripts',
		);
	}

	/**
	 * Test: Field completeness when issue detected
	 *
	 * @return array
	 */
	public static function test_result_structure_completeness(): array {
		// Setup
		remove_action( 'wp_head', 'print_emoji_detection_script' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		add_action( 'wp_head', 'print_emoji_detection_script', 7 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'print_emoji_detection_script' );

		// Verify all expected fields are present
		$expected_fields = array(
			'id',
			'title',
			'description',
			'category',
			'severity',
			'threat_level',
			'auto_fixable',
			'family',
			'family_label',
			'timestamp',
		);

		foreach ( $expected_fields as $field ) {
			if ( ! isset( $result[ $field ] ) ) {
				return array(
					'passed'  => false,
					'message' => "Missing required field: $field",
				);
			}
		}

		// Validate data types
		if ( ! is_string( $result['id'] ) ) {
			return array(
				'passed'  => false,
				'message' => 'id should be string',
			);
		}

		if ( ! is_string( $result['description'] ) ) {
			return array(
				'passed'  => false,
				'message' => 'description should be string',
			);
		}

		if ( ! is_int( $result['threat_level'] ) ) {
			return array(
				'passed'  => false,
				'message' => 'threat_level should be int, got: ' . gettype( $result['threat_level'] ),
			);
		}

		if ( ! is_bool( $result['auto_fixable'] ) ) {
			return array(
				'passed'  => false,
				'message' => 'auto_fixable should be bool',
			);
		}

		return array(
			'passed'  => true,
			'message' => 'All required fields present with correct data types',
		);
	}
}
