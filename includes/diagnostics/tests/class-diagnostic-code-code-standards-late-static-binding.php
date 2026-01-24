<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Late Static Binding Misuse
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-late-static-binding
 * Training: https://wpshadow.com/training/code-standards-late-static-binding
 */
class Diagnostic_Code_CODE_STANDARDS_LATE_STATIC_BINDING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-late-static-binding',
            'title' => __('Late Static Binding Misuse', 'wpshadow'),
            'description' => __('Detects incorrect use of static:: vs self::.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-late-static-binding',
            'training_link' => 'https://wpshadow.com/training/code-standards-late-static-binding',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE STANDARDS LATE STATIC BINDING
	 * Slug: -code-code-standards-late-static-binding
	 * File: class-diagnostic-code-code-standards-late-static-binding.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE STANDARDS LATE STATIC BINDING
	 * Slug: -code-code-standards-late-static-binding
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
	public static function test_live__code_code_standards_late_static_binding(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Code properly uses late static binding'];
		}
		$message = $result['description'] ?? 'Late static binding issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
