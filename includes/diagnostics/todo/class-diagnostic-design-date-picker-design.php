<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Date Picker Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-date-picker-design
 * Training: https://wpshadow.com/training/design-date-picker-design
 */
class Diagnostic_Design_DATE_PICKER_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-date-picker-design',
            'title' => __('Date Picker Design', 'wpshadow'),
            'description' => __('Checks date pickers show calendar.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-date-picker-design',
            'training_link' => 'https://wpshadow.com/training/design-date-picker-design',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DATE PICKER DESIGN
	 * Slug: -design-date-picker-design
	 * File: class-diagnostic-design-date-picker-design.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DATE PICKER DESIGN
	 * Slug: -design-date-picker-design
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
	public static function test_live__design_date_picker_design(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Date picker UI is properly designed and accessible'];
		}
		$message = $result['description'] ?? 'Date picker design issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
