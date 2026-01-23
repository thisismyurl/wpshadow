<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Radius Scale Adherence
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-radius-scale-adherence
 * Training: https://wpshadow.com/training/design-radius-scale-adherence
 */
class Diagnostic_Design_DESIGN_RADIUS_SCALE_ADHERENCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-radius-scale-adherence',
            'title' => __('Radius Scale Adherence', 'wpshadow'),
            'description' => __('Flags radii that are not on the radius scale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-radius-scale-adherence',
            'training_link' => 'https://wpshadow.com/training/design-radius-scale-adherence',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DESIGN RADIUS SCALE ADHERENCE
	 * Slug: -design-design-radius-scale-adherence
	 * File: class-diagnostic-design-design-radius-scale-adherence.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DESIGN RADIUS SCALE ADHERENCE
	 * Slug: -design-design-radius-scale-adherence
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
	public static function test_live__design_design_radius_scale_adherence(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Border radius values follow design system scale'];
		}
		$message = $result['description'] ?? 'Radius scale inconsistency detected';
		return ['passed' => false, 'message' => $message];
	}

}
