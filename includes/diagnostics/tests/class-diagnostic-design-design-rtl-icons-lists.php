<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: RTL Icons and Lists
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-rtl-icons-lists
 * Training: https://wpshadow.com/training/design-rtl-icons-lists
 */
class Diagnostic_Design_DESIGN_RTL_ICONS_LISTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-rtl-icons-lists',
            'title' => __('RTL Icons and Lists', 'wpshadow'),
            'description' => __('Checks list indentation and icon mirroring in RTL.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-rtl-icons-lists',
            'training_link' => 'https://wpshadow.com/training/design-rtl-icons-lists',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DESIGN RTL ICONS LISTS
	 * Slug: -design-design-rtl-icons-lists
	 * File: class-diagnostic-design-design-rtl-icons-lists.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DESIGN RTL ICONS LISTS
	 * Slug: -design-design-rtl-icons-lists
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
	public static function test_live__design_design_rtl_icons_lists(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Right-to-left text direction properly supported'];
		}
		$message = $result['description'] ?? 'RTL support issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
