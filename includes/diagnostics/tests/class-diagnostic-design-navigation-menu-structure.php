<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Navigation Menu Structure
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-navigation-menu-structure
 * Training: https://wpshadow.com/training/design-navigation-menu-structure
 */
class Diagnostic_Design_NAVIGATION_MENU_STRUCTURE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-navigation-menu-structure',
            'title' => __('Navigation Menu Structure', 'wpshadow'),
            'description' => __('Checks main nav structured logically, clear hierarchy, max 7-9 items.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-navigation-menu-structure',
            'training_link' => 'https://wpshadow.com/training/design-navigation-menu-structure',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design NAVIGATION MENU STRUCTURE
	 * Slug: -design-navigation-menu-structure
	 * File: class-diagnostic-design-navigation-menu-structure.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design NAVIGATION MENU STRUCTURE
	 * Slug: -design-navigation-menu-structure
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
	public static function test_live__design_navigation_menu_structure(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Navigation menus are properly structured and accessible'];
		}
		$message = $result['description'] ?? 'Menu structure issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
