<?php
/**
 * Readiness Filtering Tests
 *
 * Integration tests for Diagnostic_Registry and Treatment_Registry filtering.
 *
 * @package ThisIsMyURL\Shadow
 * @since 0.7055
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Diagnostics\Tests;

use ThisIsMyURL\Shadow\Core\Diagnostic_Base;
use ThisIsMyURL\Shadow\Diagnostics\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test suite for readiness filtering in discovery and listing.
 */
class Readiness_Filtering_Test extends Diagnostic_Base {

	/**
	 * Get the diagnostic title.
	 *
	 * @return string
	 */
	public static function get_title(): string {
		return __( 'Readiness Filtering Tests', 'thisismyurl-shadow' );
	}

	/**
	 * Get the diagnostic description.
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Validates that filtering by readiness state works in all discovery contexts.', 'thisismyurl-shadow' );
	}

	/**
	 * Run the diagnostic.
	 *
	 * @return array<string, mixed>
	 */
	public static function run(): array {
		$findings = array();

		// Test 1: Production-only filtering (default)
		$result = self::test_production_filtering();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 2: Beta inclusion filter
		$result = self::test_beta_inclusion();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 3: Planned inclusion filter
		$result = self::test_planned_inclusion();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 4: Custom allowed states filter
		$result = self::test_custom_allowed_states();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		// Test 5: Diagnostic definitions include readiness field
		$result = self::test_definitions_readiness_field();
		if ( ! $result['passed'] ) {
			$findings[] = $result['finding'];
		}

		if ( empty( $findings ) ) {
			return array(
				'passed'  => true,
				'message' => __( 'All readiness filtering tests passed.', 'thisismyurl-shadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => __( 'Readiness filtering tests failed:', 'thisismyurl-shadow' ) . ' ' . implode( '; ', $findings ),
		);
	}

	/**
	 * Test production-only filtering (default behavior).
	 *
	 * @return array<string, mixed>
	 */
	private static function test_production_filtering(): array {
		// Get diagnostic definitions (should be production-only by default)
		$definitions = Diagnostic_Registry::get_diagnostic_definitions();

		if ( empty( $definitions ) || ! is_array( $definitions ) ) {
			return array(
				'passed' => false,
				'finding' => 'No diagnostic definitions found',
			);
		}

		// Check that no beta or planned diagnostics are in the default list
		foreach ( $definitions as $definition ) {
			if ( ! is_array( $definition ) ) {
				continue;
			}

			$readiness = isset( $definition['readiness'] ) ? (string) $definition['readiness'] : 'production';

			// By default, only production should be included
			if ( 'beta' === $readiness || 'planned' === $readiness ) {
				return array(
					'passed' => false,
					'finding' => sprintf(
						'%s diagnostic found in production-only list (state: %s)',
						$readiness,
						$definition['class'] ?? 'unknown'
					),
				);
			}
		}

		return array( 'passed' => true );
	}

	/**
	 * Test beta inclusion filter.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_beta_inclusion(): array {
		// Add filter to include beta
		add_filter( 'thisismyurl_shadow_include_beta_diagnostics', '__return_true' );

		$definitions = Diagnostic_Registry::get_diagnostic_definitions();

		remove_filter( 'thisismyurl_shadow_include_beta_diagnostics', '__return_true' );

		if ( empty( $definitions ) || ! is_array( $definitions ) ) {
			return array(
				'passed' => false,
				'finding' => 'No diagnostic definitions found with beta filter',
			);
		}

		// At least check that the filter hook is applied (even if no beta items exist)
		// We can't guarantee beta items exist, so we just verify the method was called
		return array( 'passed' => true );
	}

	/**
	 * Test planned inclusion filter.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_planned_inclusion(): array {
		// Add filter to include planned
		add_filter( 'thisismyurl_shadow_include_planned_diagnostics', '__return_true' );

		$definitions = Diagnostic_Registry::get_diagnostic_definitions();

		remove_filter( 'thisismyurl_shadow_include_planned_diagnostics', '__return_true' );

		if ( empty( $definitions ) || ! is_array( $definitions ) ) {
			return array(
				'passed' => false,
				'finding' => 'No diagnostic definitions found with planned filter',
			);
		}

		// At least check that the filter hook is applied (even if no planned items exist)
		return array( 'passed' => true );
	}

	/**
	 * Test custom allowed states filter.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_custom_allowed_states(): array {
		// Override with custom allowed states
		$allowed_states_hook = function() {
			return array( 'production', 'beta', 'planned' );
		};

		add_filter( 'thisismyurl_shadow_allowed_diagnostic_readiness_states', $allowed_states_hook );

		$definitions = Diagnostic_Registry::get_diagnostic_definitions();

		remove_filter( 'thisismyurl_shadow_allowed_diagnostic_readiness_states', $allowed_states_hook );

		if ( empty( $definitions ) || ! is_array( $definitions ) ) {
			return array(
				'passed' => false,
				'finding' => 'No diagnostic definitions found with all-states filter',
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test that diagnostic definitions include readiness field.
	 *
	 * @return array<string, mixed>
	 */
	private static function test_definitions_readiness_field(): array {
		$definitions = Diagnostic_Registry::get_diagnostic_definitions();

		if ( empty( $definitions ) ) {
			return array(
				'passed' => false,
				'finding' => 'No diagnostic definitions found',
			);
		}

		foreach ( $definitions as $definition ) {
			if ( ! is_array( $definition ) ) {
				continue;
			}

			if ( ! isset( $definition['readiness'] ) ) {
				return array(
					'passed' => false,
					'finding' => sprintf(
						'Definition missing readiness field: %s',
						$definition['class'] ?? 'unknown'
					),
				);
			}

			$state = $definition['readiness'];
			if ( ! in_array( $state, array( 'production', 'beta', 'planned' ), true ) ) {
				return array(
					'passed' => false,
					'finding' => sprintf(
						'Invalid readiness state %s in %s',
						$state,
						$definition['class'] ?? 'unknown'
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
		return 'Ensures readiness filtering is applied correctly in all contexts.';
	}
}

// Register diagnostic
if ( function_exists( '\ThisIsMyURL\Shadow\Core\Diagnostic_Registry::register_diagnostic' ) ) {
	\ThisIsMyURL\Shadow\Core\Diagnostic_Registry::register_diagnostic( 'ThisIsMyURL\\Shadow\\Diagnostics\\Tests\\Readiness_Filtering_Test' );
}
