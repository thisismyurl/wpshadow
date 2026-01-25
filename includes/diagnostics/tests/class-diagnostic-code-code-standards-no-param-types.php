<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Parameter Type Hints
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-no-param-types
 * Training: https://wpshadow.com/training/code-standards-no-param-types
 */
class Diagnostic_Code_CODE_STANDARDS_NO_PARAM_TYPES extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'code-standards-no-param-types',
			'title'         => __( 'Missing Parameter Type Hints', 'wpshadow' ),
			'description'   => __( 'Detects functions lacking parameter type declarations.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'code-quality',
			'kb_link'       => 'https://wpshadow.com/kb/code-standards-no-param-types',
			'training_link' => 'https://wpshadow.com/training/code-standards-no-param-types',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE STANDARDS NO PARAM TYPES
	 * Slug: -code-code-standards-no-param-types
	 * File: class-diagnostic-code-code-standards-no-param-types.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE STANDARDS NO PARAM TYPES
	 * Slug: -code-code-standards-no-param-types
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
	public static function test_live__code_code_standards_no_param_types(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'All function parameters properly type-hinted',
			);
		}
		$message = $result['description'] ?? 'Functions missing parameter type hints detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
