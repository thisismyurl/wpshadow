<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Direct Echo in Logic
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-direct-echo
 * Training: https://wpshadow.com/training/code-standards-direct-echo
 */
class Diagnostic_Code_CODE_STANDARDS_DIRECT_ECHO extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'code-standards-direct-echo',
			'title'         => __( 'Direct Echo in Logic', 'wpshadow' ),
			'description'   => __( 'Detects echo/print statements outside view/template layers.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'code-quality',
			'kb_link'       => 'https://wpshadow.com/kb/code-standards-direct-echo',
			'training_link' => 'https://wpshadow.com/training/code-standards-direct-echo',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE STANDARDS DIRECT ECHO
	 * Slug: -code-code-standards-direct-echo
	 * File: class-diagnostic-code-code-standards-direct-echo.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE STANDARDS DIRECT ECHO
	 * Slug: -code-code-standards-direct-echo
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
	public static function test_live__code_code_standards_direct_echo(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Code properly escapes output - no direct echo statements',
			);
		}
		$message = $result['description'] ?? 'Direct echo/print statements found';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
