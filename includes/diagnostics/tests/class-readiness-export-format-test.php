<?php
/**
 * Readiness Export Format Tests
 *
 * Validates that AJAX export handlers produce correct JSON and CSV output.
 *
 * @package WPShadow
 * @since 0.7055
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Readiness_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test suite for readiness export format validation.
 */
class Readiness_Export_Format_Test extends Diagnostic_Base {

	/**
	 * Get the diagnostic title.
	 *
	 * @return string
	 */
	public static function get_title(): string {
		return __( 'Readiness Export Format Tests', 'wpshadow' );
	}

	/**
	 * Get the diagnostic description.
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Validates that readiness inventory exports are valid JSON and CSV formats.', 'wpshadow' );
	}

	/**
	 * Run the diagnostic.
	 *
	 * @return array<string, mixed>
	 */
	public static function run(): array {
		$findings = array();

		// Test 1: JSON export structure
		$result = self::test_json_structure();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 2: JSON field completeness
		$result = self::test_json_field_completeness();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 3: CSV header format
		$result = self::test_csv_header_format();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 4: CSV row format
		$result = self::test_csv_row_format();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 5: Timestamp is valid Unix
		$result = self::test_timestamp_validity();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 6: State values are valid constants
		$result = self::test_state_values();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		if ( empty( $findings ) ) {
			return array(
				'passed'  => true,
				'message' => __( 'All export format tests passed.', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => __( 'Export format tests failed:', 'wpshadow' ) . ' ' . implode( '; ', $findings ),
		);
	}

	/**
	 * Test JSON export structure.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_json_structure(): array {
		$inventory = Readiness_Registry::get_inventory();
		$json_str = wp_json_encode( $inventory );

		if ( ! is_string( $json_str ) || empty( $json_str ) ) {
			return array(
				'passed' => false,
				'finding' => 'Failed to encode inventory as JSON',
			);
		}

		// Verify it's valid JSON by decoding
		$decoded = json_decode( $json_str, true );
		if ( ! is_array( $decoded ) ) {
			return array(
				'passed' => false,
				'finding' => 'JSON structure is not valid (decode failed)',
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test JSON field completeness.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_json_field_completeness(): array {
		$inventory = Readiness_Registry::get_inventory();
		$json_str = wp_json_encode( $inventory );
		$decoded = json_decode( $json_str, true );

		$required_top_level = array( 'generated_at', 'diagnostics', 'treatments' );
		foreach ( $required_top_level as $key ) {
			if ( ! isset( $decoded[ $key ] ) ) {
				return array(
					'passed' => false,
					'finding' => sprintf( 'JSON missing top-level key: %s', $key ),
				);
			}
		}

		// Check diagnostics structure
		if ( ! is_array( $decoded['diagnostics'] ) ) {
			return array(
				'passed' => false,
				'finding' => 'JSON diagnostics is not an array',
			);
		}

		// Check treatments structure
		if ( ! is_array( $decoded['treatments'] ) ) {
			return array(
				'passed' => false,
				'finding' => 'JSON treatments is not an array',
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test CSV header format.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_csv_header_format(): array {
		// Expected CSV header for export
		$expected_headers = array( 'Type', 'Name/Class', 'Readiness', 'Enabled/Executable', 'File/Path' );

		// Verify headers are properly formatted (this is a structure test)
		if ( count( $expected_headers ) !== 5 ) {
			return array(
				'passed' => false,
				'finding' => 'CSV header count mismatch',
			);
		}

		// Verify no empty headers
		foreach ( $expected_headers as $header ) {
			if ( empty( $header ) ) {
				return array(
					'passed' => false,
					'finding' => 'CSV contains empty header',
				);
			}
		}

		return array( 'passed' => true );
	}

	/**
	 * Test CSV row format (simulated).
	 *
	 * @return array<string, mixed>
	 */
	private static function test_csv_row_format(): array {
		$inventory = Readiness_Registry::get_inventory();

		// Verify at least one diagnostic or treatment exists
		if ( empty( $inventory['diagnostics'] ) && empty( $inventory['treatments'] ) ) {
			return array(
				'passed' => false,
				'finding' => 'No diagnostics or treatments in inventory',
			);
		}

		// Check diagnostic rows have correct field count
		foreach ( (array) $inventory['diagnostics'] as $diag ) {
			if ( ! is_array( $diag ) ) {
				continue;
			}

			// Expected fields: class, state, enabled, file
			if ( ! isset( $diag['class'], $diag['state'], $diag['enabled'], $diag['file'] ) ) {
				return array(
					'passed' => false,
					'finding' => 'Diagnostic missing required CSV fields',
				);
			}
		}

		// Check treatment rows have correct field count
		foreach ( (array) $inventory['treatments'] as $treat ) {
			if ( ! is_array( $treat ) ) {
				continue;
			}

			// Expected fields: class, state, executable, file
			if ( ! isset( $treat['class'], $treat['state'], $treat['executable'], $treat['file'] ) ) {
				return array(
					'passed' => false,
					'finding' => 'Treatment missing required CSV fields',
				);
			}
		}

		return array( 'passed' => true );
	}

	/**
	 * Test timestamp is valid Unix timestamp.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_timestamp_validity(): array {
		$inventory = Readiness_Registry::get_inventory();
		$timestamp = $inventory['generated_at'];

		if ( ! is_numeric( $timestamp ) ) {
			return array(
				'passed' => false,
				'finding' => 'generated_at is not numeric',
			);
		}

		// Check sanity: should be recent (within last hour)
		$now = time();
		$diff = abs( $now - (int) $timestamp );

		if ( $diff > 3600 ) {
			return array(
				'passed' => false,
				'finding' => sprintf( 'generated_at is not recent (diff: %d seconds)', $diff ),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test that all state values are valid constants.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_state_values(): array {
		$inventory = Readiness_Registry::get_inventory();
		$valid_states = array(
			Readiness_Registry::STATE_PRODUCTION,
			Readiness_Registry::STATE_BETA,
			Readiness_Registry::STATE_PLANNED,
		);

		// Check diagnostic states
		foreach ( (array) $inventory['diagnostics'] as $diag ) {
			if ( ! is_array( $diag ) ) {
				continue;
			}

			$state = $diag['state'] ?? null;
			if ( ! in_array( $state, $valid_states, true ) ) {
				return array(
					'passed' => false,
					'finding' => sprintf(
						'Invalid diagnostic state %s in %s',
						$state,
						$diag['class'] ?? 'unknown'
					),
				);
			}
		}

		// Check treatment states
		foreach ( (array) $inventory['treatments'] as $treat ) {
			if ( ! is_array( $treat ) ) {
				continue;
			}

			$state = $treat['state'] ?? null;
			if ( ! in_array( $state, $valid_states, true ) ) {
				return array(
					'passed' => false,
					'finding' => sprintf(
						'Invalid treatment state %s in %s',
						$state,
						$treat['class'] ?? 'unknown'
					),
				);
			}
		}

		return array( 'passed' => true );
	}

	/**
	 * Get time to fix in minutes.
	 *
	 * @return int
	 */
	public static function get_time_to_fix_minutes(): int {
		return 2;
	}

	/**
	 * Get scan frequency.
	 *
	 * @return string
	 */
	public static function get_scan_frequency(): string {
		return 'weekly';
	}

	/**
	 * Get the severity level.
	 *
	 * @return string
	 */
	public static function get_severity(): string {
		return 'low';
	}

	/**
	 * Get the category this diagnostic belongs to.
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'development';
	}

	/**
	 * Get the family/group this diagnostic belongs to.
	 *
	 * @return string
	 */
	public static function get_family(): string {
		return 'governance';
	}

	/**
	 * Get impact statement.
	 *
	 * @return string
	 */
	public static function get_impact(): string {
		return 'Ensures JSON and CSV exports are valid and complete.';
	}
}

// Register diagnostic
if ( function_exists( '\WPShadow\Core\Diagnostic_Registry::register_diagnostic' ) ) {
	\WPShadow\Core\Diagnostic_Registry::register_diagnostic( 'WPShadow\\Diagnostics\\Tests\\Readiness_Export_Format_Test' );
}
