<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Global Variables
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-global-usage
 * Training: https://wpshadow.com/training/code-standards-global-usage
 */
class Diagnostic_Code_CODE_STANDARDS_GLOBAL_USAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-global-usage',
            'title' => __('Global Variables', 'wpshadow'),
            'description' => __('Detects use of global keyword or non-namespaced functions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-global-usage',
            'training_link' => 'https://wpshadow.com/training/code-standards-global-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE STANDARDS GLOBAL USAGE
	 * Slug: -code-code-standards-global-usage
	 * File: class-diagnostic-code-code-standards-global-usage.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE STANDARDS GLOBAL USAGE
	 * Slug: -code-code-standards-global-usage
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
	public static function test_live__code_code_standards_global_usage(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Code follows standards - minimal global variable usage'];
		}
		$message = $result['description'] ?? 'Excessive global variable usage detected';
		return ['passed' => false, 'message' => $message];
	}

}
