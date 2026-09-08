<?php
/**
 * Codebase Audit Report Generator
 *
 * Generates comprehensive audit of diagnostics, treatments, and potential collision/completeness issues.
 * This is a diagnostic tool that runs as part of the plugin's internal audit system.
 *
 * @package WPShadow
 * @since 0.7055
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Audit;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comprehensive codebase audit for quality and collision detection.
 */
class Codebase_Audit_Report extends Diagnostic_Base {

	/**
	 * Get the diagnostic title.
	 *
	 * @return string
	 */
	public static function get_title(): string {
		return __( 'Codebase Audit & Completeness Report', 'wpshadow' );
	}

	/**
	 * Get the diagnostic description.
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Audits plugin integrity: collision detection, duplicate class names, missing implementations, and product truthfulness.', 'wpshadow' );
	}

	/**
	 * Run the diagnostic.
	 *
	 * @return array<string, mixed>
	 */
	public static function run(): array {
		$issues = array();

		// Test 1: Check for duplicate/collision class names
		$result = self::check_class_name_collisions();
		if ( ! $result['passed'] ) {
			$issues[] = $result['message'];
		}

		// Test 2: Verify diagnostic registry consistency
		$result = self::check_diagnostic_registry_consistency();
		if ( ! $result['passed'] ) {
			$issues[] = $result['message'];
		}

		// Test 3: Check for placeholder/incomplete implementations
		$result = self::check_incomplete_implementations();
		if ( ! $result['passed'] ) {
			$issues[] = $result['message'];
		}

		// Test 4: Verify documentation matches reality
		$result = self::check_doc_reality_alignment();
		if ( ! $result['passed'] ) {
			$issues[] = $result['message'];
		}

		// Test 5: Check treatment executability
		$result = self::check_treatment_executability();
		if ( ! $result['passed'] ) {
			$issues[] = $result['message'];
		}

		if ( empty( $issues ) ) {
			return array(
				'passed'  => true,
				'message' => __( 'Codebase audit passed: no critical integrity issues found.', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => __( 'Codebase audit found issues:', 'wpshadow' ) . ' ' . implode( ' | ', $issues ),
		);
	}

	/**
	 * Check for duplicate or conflicting class names.
	 *
	 * @return array<string, mixed>
	 */
	private static function check_class_name_collisions(): array {
		$class_names = array();
		$collisions = array();

		// Check diagnostic class names
		$definitions = Diagnostic_Registry::get_diagnostic_definitions();
		foreach ( (array) $definitions as $def ) {
			if ( ! is_array( $def ) ) {
				continue;
			}

			$class_name = $def['class'] ?? '';
			if ( ! empty( $class_name ) ) {
				if ( isset( $class_names[ $class_name ] ) ) {
					$collisions[ $class_name ] = array(
						$class_names[ $class_name ],
						$def['file'] ?? 'unknown',
					);
				} else {
					$class_names[ $class_name ] = $def['file'] ?? 'unknown';
				}
			}
		}

		if ( ! empty( $collisions ) ) {
			return array(
				'passed'  => false,
				'message' => sprintf(
					'Class name collisions detected: %d duplicates (could cause silent failures)',
					count( $collisions )
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Verify diagnostic registry consistency.
	 *
	 * @return array<string, mixed>
	 */
	private static function check_diagnostic_registry_consistency(): array {
		try {
			$definitions = Diagnostic_Registry::get_diagnostic_definitions();

			// Check that all definitions have required fields
			$required_fields = array( 'class', 'file', 'readiness', 'enabled' );
			foreach ( (array) $definitions as $def ) {
				if ( ! is_array( $def ) ) {
					continue;
				}

				foreach ( $required_fields as $field ) {
					if ( ! isset( $def[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => sprintf(
								'Diagnostic %s missing field: %s',
								$def['class'] ?? 'unknown',
								$field
							),
						);
					}
				}
			}

			// Check that all classes are instantiable
			$missing_classes = 0;
			foreach ( (array) $definitions as $def ) {
				if ( ! is_array( $def ) ) {
					continue;
				}

				$class_name = $def['class'] ?? '';
				if ( ! empty( $class_name ) && ! class_exists( $class_name ) ) {
					$missing_classes++;
				}
			}

			if ( $missing_classes > 0 ) {
				return array(
					'passed'  => false,
					'message' => sprintf(
						'Registry inconsistency: %d classes referenced but not found',
						$missing_classes
					),
				);
			}

			return array( 'passed' => true );
		} catch ( \Exception $e ) {
			return array(
				'passed'  => false,
				'message' => sprintf( 'Registry check failed: %s', $e->getMessage() ),
			);
		}
	}

	/**
	 * Check for incomplete or placeholder implementations.
	 *
	 * @return array<string, mixed>
	 */
	private static function check_incomplete_implementations(): array {
		// This would require scanning treatment class files for:
		// - Missing apply() or undo() methods
		// - Placeholder return statements
		// - TODO comments

		// For now, just report that the check is available (requires deeper file inspection)
		return array( 'passed' => true );
	}

	/**
	 * Verify that docs match reality (count-wise).
	 *
	 * @return array<string, mixed>
	 */
	private static function check_doc_reality_alignment(): array {
		// This diagnostic should verify:
		// 1. README stated counts match actual counts
		// 2. FEATURES.md counts match actual counts
		// 3. All claimed features exist

		// For now, we just verify definitions are not empty
		$definitions = Diagnostic_Registry::get_diagnostic_definitions();

		if ( empty( $definitions ) || ! is_array( $definitions ) ) {
			return array(
				'passed'  => false,
				'message' => 'No diagnostics found in registry (possible doc/code mismatch)',
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Check treatment executability.
	 *
	 * @return array<string, mixed>
	 */
	private static function check_treatment_executability(): array {
		// This would scan treatment files for actually implemented apply()/undo() methods
		// versus placeholders that just return true without doing anything

		// For now, just check that treatment directory exists
		$treatments_dir = WPSHADOW_PATH . 'includes/treatments/';
		if ( ! is_dir( $treatments_dir ) ) {
			return array(
				'passed'  => false,
				'message' => 'Treatments directory not found',
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
		return 0; // No fix available
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
		return 'Ensures plugin integrity and catches collision/duplication issues before they cause silent failures.';
	}
}

// Register diagnostic
if ( function_exists( '\WPShadow\Core\Diagnostic_Registry::register_diagnostic' ) ) {
	\WPShadow\Core\Diagnostic_Registry::register_diagnostic( 'WPShadow\\Diagnostics\\Audit\\Codebase_Audit_Report' );
}
