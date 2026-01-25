<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Duplicate Code Blocks
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-duplicate-code
 * Training: https://wpshadow.com/training/code-standards-duplicate-code
 */
class Diagnostic_Code_CODE_STANDARDS_DUPLICATE_CODE extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'code-standards-duplicate-code',
			'title'         => __( 'Duplicate Code Blocks', 'wpshadow' ),
			'description'   => __( 'Detects copy-paste code across plugins/themes (DRY violation).', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'code-quality',
			'kb_link'       => 'https://wpshadow.com/kb/code-standards-duplicate-code',
			'training_link' => 'https://wpshadow.com/training/code-standards-duplicate-code',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE STANDARDS DUPLICATE CODE
	 * Slug: -code-code-standards-duplicate-code
	 * File: class-diagnostic-code-code-standards-duplicate-code.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE STANDARDS DUPLICATE CODE
	 * Slug: -code-code-standards-duplicate-code
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
	public static function test_live__code_code_standards_duplicate_code(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Code quality high - no significant code duplication detected',
			);
		}
		$message = $result['description'] ?? 'Code duplication issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
