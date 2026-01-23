<?php
declare(strict_types=1);
/**
 * Head Cleanup - WordPress Shortlink Diagnostic
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
 * Check if WordPress shortlink is enabled.
 *
 * Family: head-cleanup
 * Related: head-cleanup-emoji, head-cleanup-oembed, head-cleanup-rsd
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Head_Cleanup_Shortlink extends Diagnostic_Base {

	protected static $slug         = 'head-cleanup-shortlink';
	protected static $title        = 'WordPress Shortlink';
	protected static $description  = 'Checks if WordPress shortlink functionality is enabled and can be removed.';
	protected static $family       = 'head-cleanup';
	protected static $family_label = 'Head Cleanup Tasks';

	public static function check(): ?array {
		if ( ! self::is_shortlink_enabled() ) {
			return null;
		}

		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The WordPress shortlink feature is rarely used in modern sites. Removing it reduces page headers and improves performance.', 'wpshadow' ),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 10,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if shortlink is enabled
	 *
	 * @return bool
	 */
	private static function is_shortlink_enabled(): bool {
		return has_action( 'wp_head', 'wp_shortlink_wp_head' ) !== false;
	}

	/**
	 * Test: Shortlink disabled (clean state)
	 *
	 * @return array
	 */
	public static function test_shortlink_disabled(): array {
		// Setup: Remove shortlink hook if it exists
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		// Execute
		$result = self::check();

		// Verify
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => 'Correctly returned null when shortlink disabled',
			);
		}

		return array(
			'passed'  => false,
			'message' => 'Expected null for disabled shortlink, got: ' . wp_json_encode( $result ),
		);
	}

	/**
	 * Test: Shortlink enabled (WordPress default)
	 *
	 * @return array
	 */
	public static function test_shortlink_enabled(): array {
		// Setup: Ensure hook is not present first
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		// Add the shortlink hook (WordPress default)
		add_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		// Verify
		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array for enabled shortlink, got: ' . gettype( $result ),
			);
		}

		// Validate critical fields
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

		if ( ! isset( $result['threat_level'] ) || 10 !== $result['threat_level'] ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect threat_level, expected 10',
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Correctly detected shortlink enabled and returned proper array',
		);
	}

	/**
	 * Test: Lower threat level than other head-cleanup (indicates less critical)
	 *
	 * @return array
	 */
	public static function test_lower_threat_level(): array {
		// Setup
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		add_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		// Verify
		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array when shortlink enabled',
			);
		}

		if ( ! isset( $result['threat_level'] ) ) {
			return array(
				'passed'  => false,
				'message' => 'Missing threat_level',
			);
		}

		// Shortlink is least critical (10), lower than RSD (18), emoji (15), oEmbed (12)
		if ( 10 !== $result['threat_level'] ) {
			return array(
				'passed'  => false,
				'message' => 'Shortlink threat_level should be 10 (lowest priority), got: ' . $result['threat_level'],
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Shortlink correctly prioritized at threat_level 10 (lowest among head-cleanup)',
		);
	}

	/**
	 * Test: Field completeness and data types
	 *
	 * @return array
	 */
	public static function test_result_structure_validation(): array {
		// Setup
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		add_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		// Verify all expected fields
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

		// Validate specific values
		if ( 'head-cleanup-shortlink' !== $result['id'] ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect id: ' . $result['id'],
			);
		}

		if ( 'performance' !== $result['category'] ) {
			return array(
				'passed'  => false,
				'message' => 'Should be performance category, got: ' . $result['category'],
			);
		}

		if ( 'low' !== $result['severity'] ) {
			return array(
				'passed'  => false,
				'message' => 'Should be low severity, got: ' . $result['severity'],
			);
		}

		if ( ! is_bool( $result['auto_fixable'] ) ) {
			return array(
				'passed'  => false,
				'message' => 'auto_fixable should be bool',
			);
		}

		if ( true !== $result['auto_fixable'] ) {
			return array(
				'passed'  => false,
				'message' => 'shortlink should be auto_fixable',
			);
		}

		return array(
			'passed'  => true,
			'message' => 'All fields present and values correct',
		);
	}

	/**
	 * Test: Hook detection at any priority
	 *
	 * @return array
	 */
	public static function test_detects_at_any_priority(): array {
		// Setup: Add shortlink at non-standard priority
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		add_action( 'wp_head', 'wp_shortlink_wp_head', 20 ); // Not priority 10

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		// Verify - should detect at any priority
		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Failed to detect shortlink at non-standard priority',
			);
		}

		if ( 'head-cleanup-shortlink' !== $result['id'] ) {
			return array(
				'passed'  => false,
				'message' => 'Detected but incorrect id',
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Correctly detected shortlink at non-standard hook priority',
		);
	}
}
