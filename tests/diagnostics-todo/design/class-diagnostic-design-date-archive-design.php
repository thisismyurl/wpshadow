<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Date Archive Design
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-date-archive-design
 * Training: https://wpshadow.com/training/design-date-archive-design
 */
class Diagnostic_Design_DATE_ARCHIVE_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-date-archive-design',
            'title' => __('Date Archive Design', 'wpshadow'),
            'description' => __('Validates year/month archive page design.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-date-archive-design',
            'training_link' => 'https://wpshadow.com/training/design-date-archive-design',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DATE ARCHIVE DESIGN
	 * Slug: -design-date-archive-design
	 * File: class-diagnostic-design-date-archive-design.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DATE ARCHIVE DESIGN
	 * Slug: -design-date-archive-design
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
	public static function test_live__design_date_archive_design(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Date archive pages are properly designed'];
		}
		$message = $result['description'] ?? 'Date archive design issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
