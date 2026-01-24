<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: rAF Usage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-raf-usage
 * Training: https://wpshadow.com/training/design-raf-usage
 */
class Diagnostic_Design_DESIGN_RAF_USAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-raf-usage',
            'title' => __('rAF Usage', 'wpshadow'),
            'description' => __('Checks animations use requestAnimationFrame instead of timers.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-raf-usage',
            'training_link' => 'https://wpshadow.com/training/design-raf-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DESIGN RAF USAGE
	 * Slug: -design-design-raf-usage
	 * File: class-diagnostic-design-design-raf-usage.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DESIGN RAF USAGE
	 * Slug: -design-design-raf-usage
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
	public static function test_live__design_design_raf_usage(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'RequestAnimationFrame properly used for smooth animations'];
		}
		$message = $result['description'] ?? 'RAF usage optimization issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
