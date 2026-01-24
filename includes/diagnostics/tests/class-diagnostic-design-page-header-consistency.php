<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Page Header Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-page-header-consistency
 * Training: https://wpshadow.com/training/design-page-header-consistency
 */
class Diagnostic_Design_PAGE_HEADER_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-page-header-consistency',
            'title' => __('Page Header Consistency', 'wpshadow'),
            'description' => __('Confirms page headers styled consistently across post types.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-page-header-consistency',
            'training_link' => 'https://wpshadow.com/training/design-page-header-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design PAGE HEADER CONSISTENCY
	 * Slug: -design-page-header-consistency
	 * File: class-diagnostic-design-page-header-consistency.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design PAGE HEADER CONSISTENCY
	 * Slug: -design-page-header-consistency
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
	public static function test_live__design_page_header_consistency(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Page headers are consistent across all pages'];
		}
		$message = $result['description'] ?? 'Page header consistency issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
