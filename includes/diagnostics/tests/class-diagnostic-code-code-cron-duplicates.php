<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Duplicate Cron Events
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-cron-duplicates
 * Training: https://wpshadow.com/training/code-cron-duplicates
 */
class Diagnostic_Code_CODE_CRON_DUPLICATES extends Diagnostic_Base {
    public static function check(): ?array {
        // Get all scheduled cron events
        $crons = \_get_cron_array();
        if (empty($crons)) {
            return null; // No cron events, healthy
        }
        
        // Track event hooks and their timestamps
        $event_counts = [];
        foreach ($crons as $timestamp => $cron) {
            foreach ($cron as $hook => $events) {
                if (!isset($event_counts[$hook])) {
                    $event_counts[$hook] = 0;
                }
                $event_counts[$hook] += count($events);
            }
        }
        
        // Find hooks scheduled more than once
        $duplicates = [];
        foreach ($event_counts as $hook => $count) {
            if ($count > 1) {
                $duplicates[$hook] = $count;
            }
        }
        
        if (empty($duplicates)) {
            return null; // No duplicates found, healthy
        }
        
        return [
            'id' => 'code-cron-duplicates',
            'title' => __('Duplicate Cron Events', 'wpshadow'),
            'description' => sprintf(
                __('Found %d cron event(s) scheduled multiple times: %s', 'wpshadow'),
                count($duplicates),
                implode(', ', array_keys($duplicates))
            ),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-cron-duplicates',
            'training_link' => 'https://wpshadow.com/training/code-cron-duplicates',
            'auto_fixable' => false,
            'threat_level' => 6,
            'data' => [
                'duplicates' => $duplicates,
            ],
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE CRON DUPLICATES
	 * Slug: -code-code-cron-duplicates
	 * File: class-diagnostic-code-code-cron-duplicates.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE CRON DUPLICATES
	 * Slug: -code-code-cron-duplicates
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
	public static function test_live__code_code_cron_duplicates(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}
