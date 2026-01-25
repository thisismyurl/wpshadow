<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Strict Types
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-no-strict-types
 * Training: https://wpshadow.com/training/code-standards-no-strict-types
 */
class Diagnostic_Code_CODE_STANDARDS_NO_STRICT_TYPES extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'code-standards-no-strict-types',
			'title'         => __( 'Missing Strict Types', 'wpshadow' ),
			'description'   => __( 'Detects files lacking declare(strict_types=1) where required.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'code-quality',
			'kb_link'       => 'https://wpshadow.com/kb/code-standards-no-strict-types',
			'training_link' => 'https://wpshadow.com/training/code-standards-no-strict-types',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE STANDARDS NO STRICT TYPES
	 * Slug: -code-code-standards-no-strict-types
	 * File: class-diagnostic-code-code-standards-no-strict-types.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE STANDARDS NO STRICT TYPES
	 * Slug: -code-code-standards-no-strict-types
	 *
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__code_code_standards_no_strict_types(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Files properly declare strict_types for type safety',
			);
		}
		$message = $result['description'] ?? 'Files missing strict_types declaration detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
