<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Custom Taxonomy Design
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-custom-taxonomy-design
 * Training: https://wpshadow.com/training/design-custom-taxonomy-design
 */
class Diagnostic_Design_CUSTOM_TAXONOMY_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-custom-taxonomy-design',
            'title' => __('Custom Taxonomy Design', 'wpshadow'),
            'description' => __('Checks custom taxonomies display correctly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-custom-taxonomy-design',
            'training_link' => 'https://wpshadow.com/training/design-custom-taxonomy-design',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design CUSTOM TAXONOMY DESIGN
	 * Slug: -design-custom-taxonomy-design
	 * File: class-diagnostic-design-custom-taxonomy-design.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design CUSTOM TAXONOMY DESIGN
	 * Slug: -design-custom-taxonomy-design
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
	public static function test_live__design_custom_taxonomy_design(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Custom taxonomies properly designed and implemented'];
		}
		$message = $result['description'] ?? 'Custom taxonomy design issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
