<?php
declare(strict_types=1);
/**
 * Head Cleanup - RSD Link Diagnostic
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
 * Check if RSD (Really Simple Discovery) link is enabled.
 *
 * Family: head-cleanup
 * Related: head-cleanup-emoji, head-cleanup-oembed, head-cleanup-shortlink
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Head_Cleanup_RSD extends Diagnostic_Base {

	protected static $slug         = 'head-cleanup-rsd';
	protected static $title        = 'RSD (Really Simple Discovery) Link';
	protected static $description  = 'Checks if WordPress RSD link is enabled and can be removed.';
	protected static $family       = 'head-cleanup';
	protected static $family_label = 'Head Cleanup Tasks';

	public static function check(): ?array {
		if ( ! self::is_rsd_enabled() ) {
			return null;
		}

		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The RSD link is legacy from the XML-RPC era and is unnecessary for modern WordPress sites. Removing it improves security and reduces page noise.', 'wpshadow' ),
			'category'     => 'security',
			'severity'     => 'low',
			'threat_level' => 18,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if RSD link is enabled
	 *
	 * @return bool
	 */
	private static function is_rsd_enabled(): bool {
		return has_action( 'wp_head', 'rsd_link' ) !== false;
	}

	/**
	 * Test: RSD link disabled (clean state)
	 *
	 * @return array
	 */
	public static function test_rsd_disabled(): array {
		// Setup: Remove RSD hook if it exists
		remove_action( 'wp_head', 'rsd_link' );

		// Execute
		$result = self::check();

		// Verify
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => 'Correctly returned null when RSD link disabled',
			);
		}

		return array(
			'passed'  => false,
			'message' => 'Expected null for disabled RSD, got: ' . wp_json_encode( $result ),
		);
	}

	/**
	 * Test: RSD link enabled (legacy WordPress setup)
	 *
	 * @return array
	 */
	public static function test_rsd_enabled(): array {
		// Setup: Ensure hook is not present first
		remove_action( 'wp_head', 'rsd_link' );

		// Add the RSD hook (WordPress default behavior)
		add_action( 'wp_head', 'rsd_link', 10 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'rsd_link' );

		// Verify
		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array for enabled RSD, got: ' . gettype( $result ),
			);
		}

		// Validate critical fields
		if ( ! isset( $result['id'] ) || $result['id'] !== self::$slug ) {
			return array(
				'passed'  => false,
				'message' => 'Missing or incorrect id field',
			);
		}

		if ( ! isset( $result['category'] ) || $result['category'] !== 'security' ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect category, expected security (not performance)',
			);
		}

		if ( ! isset( $result['severity'] ) || $result['severity'] !== 'low' ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect severity, expected low',
			);
		}

		if ( ! isset( $result['threat_level'] ) || 18 !== $result['threat_level'] ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect threat_level, expected 18',
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Correctly detected RSD link enabled and returned proper array',
		);
	}

	/**
	 * Test: Category is security (not performance like other head-cleanup)
	 *
	 * @return array
	 */
	public static function test_security_category_emphasis(): array {
		// Setup
		remove_action( 'wp_head', 'rsd_link' );
		add_action( 'wp_head', 'rsd_link', 10 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'rsd_link' );

		// Verify
		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array when RSD enabled',
			);
		}

		// RSD is unique in head-cleanup: it's security, not performance
		if ( 'security' !== $result['category'] ) {
			return array(
				'passed'  => false,
				'message' => 'RSD should have security category, got: ' . $result['category'],
			);
		}

		if ( ! strpos( $result['description'], 'security' ) && ! strpos( $result['description'], 'Security' ) ) {
			return array(
				'passed'  => false,
				'message' => 'Description should mention security aspect',
			);
		}

		// Threat level should be 18 (security-related)
		if ( 18 !== $result['threat_level'] ) {
			return array(
				'passed'  => false,
				'message' => 'Security-focused threat_level should be 18, got: ' . $result['threat_level'],
			);
		}

		return array(
			'passed'  => true,
			'message' => 'RSD correctly categorized as security (higher priority than other head-cleanup items)',
		);
	}

	/**
	 * Test: All required fields present
	 *
	 * @return array
	 */
	public static function test_result_structure_complete(): array {
		// Setup
		remove_action( 'wp_head', 'rsd_link' );
		add_action( 'wp_head', 'rsd_link', 10 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'rsd_link' );

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

		// Validate data types
		if ( ! is_string( $result['id'] ) || ! is_string( $result['title'] ) ) {
			return array(
				'passed'  => false,
				'message' => 'id and title should be strings',
			);
		}

		if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
			return array(
				'passed'  => false,
				'message' => 'threat_level should be int between 0-100',
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
			'message' => 'All fields present with correct data types',
		);
	}
}
