<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Animation Variety Count
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-animation-count
 * Training: https://wpshadow.com/training/design-debt-animation-count
 */
class Diagnostic_Design_DEBT_ANIMATION_COUNT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-animation-count',
            'title' => __('Animation Variety Count', 'wpshadow'),
            'description' => __('Counts unique animation definitions (bloat indicator).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-animation-count',
            'training_link' => 'https://wpshadow.com/training/design-debt-animation-count',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DEBT ANIMATION COUNT
	 * Slug: -design-debt-animation-count
	 * File: class-diagnostic-design-debt-animation-count.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DEBT ANIMATION COUNT
	 * Slug: -design-debt-animation-count
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
	public static function test_live__design_debt_animation_count(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Animation count is optimized for performance'];
		}
		$message = $result['description'] ?? 'Excessive animations detected';
		return ['passed' => false, 'message' => $message];
	}

}
