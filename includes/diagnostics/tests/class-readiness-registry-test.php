<?php
/**
 * Readiness Registry Tests
 *
 * Tests for lifecycle state resolution, inventory building, and filtering logic.
 *
 * @package WPShadow
 * @since 0.7055
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Readiness_Registry;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test suite for Readiness_Registry state resolution and inventory.
 */
class Readiness_Registry_Test extends Diagnostic_Base {

	/**
	 * Get the diagnostic title.
	 *
	 * @return string
	 */
	public static function get_title(): string {
		return __( 'Readiness Registry Tests', 'wpshadow' );
	}

	/**
	 * Get the diagnostic description.
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Validates readiness state resolution and inventory building logic.', 'wpshadow' );
	}

	/**
	 * Run the diagnostic.
	 *
	 * @return array<string, mixed>
	 */
	public static function run(): array {
		$findings = array();

		// Test 1: Path-based production detection
		$result = self::test_production_diagnostics_paths();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 2: Path-based beta detection
		$result = self::test_beta_diagnostics_paths();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 3: Path-based planned detection
		$result = self::test_planned_diagnostics_paths();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 4: Reflection-based treatment state (production)
		$result = self::test_production_treatment_reflection();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 5: Reflection-based treatment state (beta)
		$result = self::test_beta_treatment_reflection();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 6: Reflection-based treatment state (planned)
		$result = self::test_planned_treatment_reflection();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 7: Inventory structure validation
		$result = self::test_inventory_structure();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 8: Diagnostic state filter hook
		$result = self::test_diagnostic_state_filter_hook();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 9: Treatment state filter hook
		$result = self::test_treatment_state_filter_hook();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		if ( empty( $findings ) ) {
			return array(
				'passed'  => true,
				'message' => __( 'All readiness registry tests passed.', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => __( 'Readiness registry tests failed:', 'wpshadow' ) . ' ' . implode( '; ', $findings ),
		);
	}

	/**
	 * Test production diagnostic path detection.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_production_diagnostics_paths(): array {
		$test_paths = array(
			'includes/diagnostics/tests/class-something.php' => true,
			'includes/diagnostics/verified/class-something.php' => true,
		);

		foreach ( $test_paths as $path => $should_be_production ) {
			$state = Readiness_Registry::get_diagnostic_state( 'Test_Class', $path );
			$is_production = 'production' === $state;

			if ( $is_production !== $should_be_production ) {
				return array(
					'passed' => false,
					'finding' => sprintf(
						'Production path detection failed for %s (got %s, expected production)',
						$path,
						$state
					),
				);
			}
		}

		return array( 'passed' => true );
	}

	/**
	 * Test beta diagnostic path detection.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_beta_diagnostics_paths(): array {
		$test_paths = array(
			'includes/diagnostics/help/class-something.php' => true,
		);

		foreach ( $test_paths as $path => $should_be_beta ) {
			$state = Readiness_Registry::get_diagnostic_state( 'Test_Class', $path );
			$is_beta = 'beta' === $state;

			if ( $is_beta !== $should_be_beta ) {
				return array(
					'passed' => false,
					'finding' => sprintf(
						'Beta path detection failed for %s (got %s, expected beta)',
						$path,
						$state
					),
				);
			}
		}

		return array( 'passed' => true );
	}

	/**
	 * Test planned diagnostic path detection.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_planned_diagnostics_paths(): array {
		$test_paths = array(
			'includes/diagnostics/todo/class-something.php' => true,
		);

		foreach ( $test_paths as $path => $should_be_planned ) {
			$state = Readiness_Registry::get_diagnostic_state( 'Test_Class', $path );
			$is_planned = 'planned' === $state;

			if ( $is_planned !== $should_be_planned ) {
				return array(
					'passed' => false,
					'finding' => sprintf(
						'Planned path detection failed for %s (got %s, expected planned)',
						$path,
						$state
					),
				);
			}
		}

		return array( 'passed' => true );
	}

	/**
	 * Test production treatment reflection (has both apply and undo).
	 *
	 * @return array<string, mixed>
	 */
	private static function test_production_treatment_reflection(): array {
		// Create a mock treatment class with both methods
		$mock_class = 'WPShadow\\Core\\Treatment_Base';

		if ( ! class_exists( $mock_class ) ) {
			return array(
				'passed' => false,
				'finding' => sprintf( 'Mock treatment class %s not found', $mock_class ),
			);
		}

		// Treatment_Base is production (has apply and undo)
		$state = Readiness_Registry::get_treatment_state( $mock_class );

		if ( 'production' !== $state ) {
			return array(
				'passed' => false,
				'finding' => sprintf(
					'Production treatment reflection failed: got %s, expected production',
					$state
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test beta treatment reflection (has only apply or only undo).
	 *
	 * @return array<string, mixed>
	 */
	private static function test_beta_treatment_reflection(): array {
		// Non-existent class should return planned, not beta
		$state = Readiness_Registry::get_treatment_state( 'NonExistent\\Mock_Treatment' );

		if ( 'planned' !== $state ) {
			return array(
				'passed' => false,
				'finding' => sprintf(
					'Non-existent class should be planned, got %s',
					$state
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test planned treatment reflection (has neither apply nor undo).
	 *
	 * @return array<string, mixed>
	 */
	private static function test_planned_treatment_reflection(): array {
		// This diagnostic class (has run, no apply/undo) should be planned when tested as treatment
		$state = Readiness_Registry::get_treatment_state( 'WPShadow\\Diagnostics\\Tests\\Readiness_Registry_Test' );

		if ( 'planned' !== $state ) {
			return array(
				'passed' => false,
				'finding' => sprintf(
					'Diagnostic class tested as treatment should be planned, got %s',
					$state
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test inventory structure and completeness.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_inventory_structure(): array {
		$inventory = Readiness_Registry::get_inventory();

		// Check top-level keys
		$required_keys = array( 'generated_at', 'diagnostics', 'treatments' );
		foreach ( $required_keys as $key ) {
			if ( ! isset( $inventory[ $key ] ) ) {
				return array(
					'passed' => false,
					'finding' => sprintf( 'Inventory missing required key: %s', $key ),
				);
			}
		}

		// Check generated_at is numeric
		if ( ! is_numeric( $inventory['generated_at'] ) ) {
			return array(
				'passed' => false,
				'finding' => 'generated_at is not numeric',
			);
		}

		// Check diagnostics and treatments are arrays
		if ( ! is_array( $inventory['diagnostics'] ) || ! is_array( $inventory['treatments'] ) ) {
			return array(
				'passed' => false,
				'finding' => 'diagnostics or treatments is not an array',
			);
		}

		// Check each diagnostic has required fields
		foreach ( $inventory['diagnostics'] as $diag ) {
			if ( ! is_array( $diag ) ) {
				continue;
			}
			$required = array( 'class', 'file', 'state', 'enabled' );
			foreach ( $required as $key ) {
				if ( ! isset( $diag[ $key ] ) ) {
					return array(
						'passed' => false,
						'finding' => sprintf( 'Diagnostic missing field: %s in %s', $key, $diag['class'] ?? 'unknown' ),
					);
				}
			}
		}

		return array( 'passed' => true );
	}

	/**
	 * Test diagnostic state filter hook.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_diagnostic_state_filter_hook(): array {
		// Set up filter to override state
		$filter_called = false;
		$hook = function( $state, $class_name, $file_path ) use ( &$filter_called ) {
			$filter_called = true;
			return 'beta'; // Force beta regardless of path
		};

		add_filter( 'wpshadow_diagnostic_readiness_state', $hook, 10, 3 );

		$state = Readiness_Registry::get_diagnostic_state(
			'Test_Class',
			'includes/diagnostics/tests/class-something.php' // Would be production normally
		);

		remove_filter( 'wpshadow_diagnostic_readiness_state', $hook );

		if ( ! $filter_called ) {
			return array(
				'passed' => false,
				'finding' => 'wpshadow_diagnostic_readiness_state filter was not called',
			);
		}

		if ( 'beta' !== $state ) {
			return array(
				'passed' => false,
				'finding' => sprintf( 'Filter override failed: expected beta, got %s', $state ),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test treatment state filter hook.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_treatment_state_filter_hook(): array {
		// Set up filter to override state
		$filter_called = false;
		$hook = function( $state, $class_name ) use ( &$filter_called ) {
			$filter_called = true;
			return 'planned'; // Force planned regardless of class
		};

		add_filter( 'wpshadow_treatment_readiness_state', $hook, 10, 2 );

		$state = Readiness_Registry::get_treatment_state( 'WPShadow\\Core\\Treatment_Base' ); // Would be production normally

		remove_filter( 'wpshadow_treatment_readiness_state', $hook );

		if ( ! $filter_called ) {
			return array(
				'passed' => false,
				'finding' => 'wpshadow_treatment_readiness_state filter was not called',
			);
		}

		if ( 'planned' !== $state ) {
			return array(
				'passed' => false,
				'finding' => sprintf( 'Filter override failed: expected planned, got %s', $state ),
			);
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
	 * @return string One of: critical, high, medium, low
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
		return 'Ensures readiness registry state resolution is correct.';
	}
}

// Register diagnostic
if ( function_exists( '\WPShadow\Core\Diagnostic_Registry::register_diagnostic' ) ) {
	\WPShadow\Core\Diagnostic_Registry::register_diagnostic( 'WPShadow\\Diagnostics\\Tests\\Readiness_Registry_Test' );
}
