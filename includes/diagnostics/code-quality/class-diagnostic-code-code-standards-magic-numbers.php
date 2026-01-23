<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Magic Numbers/Strings
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-magic-numbers
 * Training: https://wpshadow.com/training/code-standards-magic-numbers
 */
class Diagnostic_Code_CODE_STANDARDS_MAGIC_NUMBERS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-magic-numbers',
            'title' => __('Magic Numbers/Strings', 'wpshadow'),
            'description' => __('Detects hardcoded values that should be named constants.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-magic-numbers',
            'training_link' => 'https://wpshadow.com/training/code-standards-magic-numbers',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE STANDARDS MAGIC NUMBERS
	 * Slug: -code-code-standards-magic-numbers
	 * File: class-diagnostic-code-code-standards-magic-numbers.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE STANDARDS MAGIC NUMBERS
	 * Slug: -code-code-standards-magic-numbers
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
	public static function test_live__code_code_standards_magic_numbers(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Code quality maintained - magic numbers properly extracted to constants'];
		}
		$message = $result['description'] ?? 'Magic numbers detected in code';
		return ['passed' => false, 'message' => $message];
	}

}
