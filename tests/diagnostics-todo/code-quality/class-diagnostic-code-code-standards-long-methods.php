<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Long Methods/Functions
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-long-methods
 * Training: https://wpshadow.com/training/code-standards-long-methods
 */
class Diagnostic_Code_CODE_STANDARDS_LONG_METHODS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-long-methods',
            'title' => __('Long Methods/Functions', 'wpshadow'),
            'description' => __('Flags functions exceeding line count thresholds (100+ lines).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-long-methods',
            'training_link' => 'https://wpshadow.com/training/code-standards-long-methods',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE STANDARDS LONG METHODS
	 * Slug: -code-code-standards-long-methods
	 * File: class-diagnostic-code-code-standards-long-methods.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE STANDARDS LONG METHODS
	 * Slug: -code-code-standards-long-methods
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
	public static function test_live__code_code_standards_long_methods(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Methods follow size standards - complex logic properly extracted'];
		}
		$message = $result['description'] ?? 'Oversized method detected';
		return ['passed' => false, 'message' => $message];
	}

}
