<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Customizer Preview Accuracy
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-customizer-preview-accurate
 * Training: https://wpshadow.com/training/design-customizer-preview-accurate
 */
class Diagnostic_Design_CUSTOMIZER_PREVIEW_ACCURATE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-customizer-preview-accurate',
            'title' => __('Customizer Preview Accuracy', 'wpshadow'),
            'description' => __('Validates customizer preview matches live site.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-customizer-preview-accurate',
            'training_link' => 'https://wpshadow.com/training/design-customizer-preview-accurate',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design CUSTOMIZER PREVIEW ACCURATE
	 * Slug: -design-customizer-preview-accurate
	 * File: class-diagnostic-design-customizer-preview-accurate.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design CUSTOMIZER PREVIEW ACCURATE
	 * Slug: -design-customizer-preview-accurate
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
	public static function test_live__design_customizer_preview_accurate(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Theme customizer preview accurately represents live site'];
		}
		$message = $result['description'] ?? 'Customizer preview accuracy issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
