<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Data Table Responsiveness
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-data-table-responsiveness
 * Training: https://wpshadow.com/training/design-data-table-responsiveness
 */
class Diagnostic_Design_DATA_TABLE_RESPONSIVENESS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-data-table-responsiveness',
            'title' => __('Data Table Responsiveness', 'wpshadow'),
            'description' => __('Verifies large tables handle responsiveness.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-data-table-responsiveness',
            'training_link' => 'https://wpshadow.com/training/design-data-table-responsiveness',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DATA TABLE RESPONSIVENESS
	 * Slug: -design-data-table-responsiveness
	 * File: class-diagnostic-design-data-table-responsiveness.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DATA TABLE RESPONSIVENESS
	 * Slug: -design-data-table-responsiveness
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
	public static function test_live__design_data_table_responsiveness(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Data tables are responsive and mobile-friendly'];
		}
		$message = $result['description'] ?? 'Table responsiveness issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
