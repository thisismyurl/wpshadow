<?php
declare(strict_types=1);
/**
 * Head Cleanup - oEmbed Discovery Diagnostic
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
 * Check if oEmbed discovery links are enabled.
 *
 * Family: head-cleanup
 * Related: head-cleanup-emoji, head-cleanup-rsd, head-cleanup-shortlink
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Head_Cleanup_OEmbed extends Diagnostic_Base {

	protected static $slug         = 'head-cleanup-oembed';
	protected static $title        = 'oEmbed Discovery Links';
	protected static $description  = 'Checks if WordPress oEmbed discovery links are enabled and can be removed.';
	protected static $family       = 'head-cleanup';
	protected static $family_label = 'Head Cleanup Tasks';

	public static function check(): ?array {
		if ( ! self::is_oembed_enabled() ) {
			return null;
		}

		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'oEmbed discovery links are rarely used by modern sites. Removing them reduces page bloat and HTTP headers.', 'wpshadow' ),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 12,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if oEmbed discovery is enabled
	 *
	 * @return bool
	 */
	private static function is_oembed_enabled(): bool {
		return has_action( 'wp_head', 'wp_oembed_add_discovery_links' ) !== false;
	}

	/**
	 * Test: oEmbed discovery disabled (clean state)
	 *
	 * @return array
	 */
	public static function test_oembed_disabled(): array {
		// Setup: Remove oEmbed hook if it exists
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Execute
		$result = self::check();

		// Verify
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => 'Correctly returned null when oEmbed discovery disabled',
			);
		}

		return array(
			'passed'  => false,
			'message' => 'Expected null for disabled oEmbed, got: ' . wp_json_encode( $result ),
		);
	}

	/**
	 * Test: oEmbed discovery enabled (typical WordPress setup)
	 *
	 * @return array
	 */
	public static function test_oembed_enabled(): array {
		// Setup: Ensure hook is not present first
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Add the oEmbed hook (typical WordPress setup)
		add_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Verify
		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array for enabled oEmbed, got: ' . gettype( $result ),
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

		if ( ! isset( $result['threat_level'] ) || 12 !== $result['threat_level'] ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect threat_level, expected 12',
			);
		}

		if ( ! isset( $result['auto_fixable'] ) || true !== $result['auto_fixable'] ) {
			return array(
				'passed'  => false,
				'message' => 'Expected auto_fixable to be true',
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Correctly detected oEmbed discovery enabled and returned proper array',
		);
	}

	/**
	 * Test: Field completeness and values
	 *
	 * @return array
	 */
	public static function test_result_structure_when_enabled(): array {
		// Setup
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		add_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

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

		// Validate field content matches expectation
		if ( 'head-cleanup-oembed' !== $result['id'] ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect id: ' . $result['id'],
			);
		}

		if ( 'oEmbed Discovery Links' !== $result['title'] ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect title: ' . $result['title'],
			);
		}

		if ( ! strpos( $result['description'], 'oEmbed' ) ) {
			return array(
				'passed'  => false,
				'message' => 'Description missing oEmbed reference',
			);
		}

		if ( 'low' !== $result['severity'] ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect severity: ' . $result['severity'],
			);
		}

		if ( 12 !== $result['threat_level'] ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect threat level: ' . $result['threat_level'],
			);
		}

		if ( true !== $result['auto_fixable'] ) {
			return array(
				'passed'  => false,
				'message' => 'auto_fixable should be true',
			);
		}

		return array(
			'passed'  => true,
			'message' => 'All fields present and correct',
		);
	}

	/**
	 * Test: Hook priority detection (ensure we detect at any priority)
	 *
	 * @return array
	 */
	public static function test_oembed_at_different_priority(): array {
		// Setup: Remove and add at different priority
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Add at priority 15 (not default 10)
		add_action( 'wp_head', 'wp_oembed_add_discovery_links', 15 );

		// Execute
		$result = self::check();

		// Cleanup
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Verify - should still detect regardless of priority
		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Failed to detect oEmbed at non-standard priority',
			);
		}

		if ( 'head-cleanup-oembed' !== $result['id'] ) {
			return array(
				'passed'  => false,
				'message' => 'Incorrect id when detected at non-standard priority',
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Correctly detected oEmbed at non-standard priority',
		);
	}
}
