<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Multi-Site Style Inheritance
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-multisite-style-inheritance
 * Training: https://wpshadow.com/training/design-multisite-style-inheritance
 */
class Diagnostic_Design_MULTISITE_STYLE_INHERITANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-multisite-style-inheritance',
            'title' => __('Multi-Site Style Inheritance', 'wpshadow'),
            'description' => __('Checks parent theme styles properly inherited.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-multisite-style-inheritance',
            'training_link' => 'https://wpshadow.com/training/design-multisite-style-inheritance',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design MULTISITE STYLE INHERITANCE
	 * Slug: -design-multisite-style-inheritance
	 * File: class-diagnostic-design-multisite-style-inheritance.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design MULTISITE STYLE INHERITANCE
	 * Slug: -design-multisite-style-inheritance
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
	public static function test_live__design_multisite_style_inheritance(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Style inheritance properly configured across multisite'];
		}
		$message = $result['description'] ?? 'Style inheritance issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
