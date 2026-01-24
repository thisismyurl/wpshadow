<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Accessibility Debt Ratio
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-accessibility-fixes
 * Training: https://wpshadow.com/training/design-debt-accessibility-fixes
 */
class Diagnostic_Design_DEBT_ACCESSIBILITY_FIXES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-accessibility-fixes',
            'title' => __('Accessibility Debt Ratio', 'wpshadow'),
            'description' => __('Counts a11y issues fixed vs still pending.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-accessibility-fixes',
            'training_link' => 'https://wpshadow.com/training/design-debt-accessibility-fixes',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DEBT ACCESSIBILITY FIXES
	 * Slug: -design-debt-accessibility-fixes
	 * File: class-diagnostic-design-debt-accessibility-fixes.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DEBT ACCESSIBILITY FIXES
	 * Slug: -design-debt-accessibility-fixes
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
	public static function test_live__design_debt_accessibility_fixes(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Accessibility improvements are being actively addressed'];
		}
		$message = $result['description'] ?? 'Accessibility debt backlog issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
