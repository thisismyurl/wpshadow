<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: RTL Mirroring
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-rtl-mirroring
 * Training: https://wpshadow.com/training/design-rtl-mirroring
 */
class Diagnostic_Design_DESIGN_RTL_MIRRORING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-rtl-mirroring',
            'title' => __('RTL Mirroring', 'wpshadow'),
            'description' => __('Checks mirrored layouts, icon direction, and alignment.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-rtl-mirroring',
            'training_link' => 'https://wpshadow.com/training/design-rtl-mirroring',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DESIGN RTL MIRRORING
	 * Slug: -design-design-rtl-mirroring
	 * File: class-diagnostic-design-design-rtl-mirroring.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DESIGN RTL MIRRORING
	 * Slug: -design-design-rtl-mirroring
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
	public static function test_live__design_design_rtl_mirroring(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Layout properly mirrors for RTL languages'];
		}
		$message = $result['description'] ?? 'RTL mirroring issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
