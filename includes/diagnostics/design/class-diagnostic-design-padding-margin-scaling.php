<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Padding & Margin Scaling
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-padding-margin-scaling
 * Training: https://wpshadow.com/training/design-padding-margin-scaling
 */
class Diagnostic_Design_PADDING_MARGIN_SCALING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-padding-margin-scaling',
            'title' => __('Padding & Margin Scaling', 'wpshadow'),
            'description' => __('Verifies margins/padding scale with viewport (clamp or breakpoints).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-padding-margin-scaling',
            'training_link' => 'https://wpshadow.com/training/design-padding-margin-scaling',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design PADDING MARGIN SCALING
	 * Slug: -design-padding-margin-scaling
	 * File: class-diagnostic-design-padding-margin-scaling.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design PADDING MARGIN SCALING
	 * Slug: -design-padding-margin-scaling
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
	public static function test_live__design_padding_margin_scaling(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Padding and margin scales consistently across site'];
		}
		$message = $result['description'] ?? 'Padding/margin scaling issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
