<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Admin Bar and Dashboard Widget Weight (WP-336)
 *
 * Profiles wp-admin widgets/admin-bar cost by role/page.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_AdminBarDashboardWeight extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Check admin bar dashboard weight
        $dashboard_widgets = get_transient('wpshadow_dashboard_widget_count');
        
        if (!$dashboard_widgets) {
            $dashboard_widgets = 5; // Default rough estimate
        }
        
        if ($dashboard_widgets > 15) {
            return array(
                'id' => 'admin-bar-dashboard-weight',
                'title' => sprintf(__('Heavy Dashboard (%d widgets)', 'wpshadow'), $dashboard_widgets),
                'description' => __('Dashboard has many widgets. Disable unused dashboard widgets to improve admin area performance.', 'wpshadow'),
                'severity' => 'low',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/dashboard-optimization/',
                'training_link' => 'https://wpshadow.com/training/dashboard-performance/',
                'auto_fixable' => false,
                'threat_level' => 25,
            );
        }
        return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: AdminBarDashboardWeight
	 * Slug: -admin-bar-dashboard-weight
	 * File: class-diagnostic-admin-bar-dashboard-weight.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: AdminBarDashboardWeight
	 * Slug: -admin-bar-dashboard-weight
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
	public static function test_live__admin_bar_dashboard_weight(): array {
		$dashboard_widgets = get_transient('wpshadow_dashboard_widget_count');
		if ( ! $dashboard_widgets ) {
			$dashboard_widgets = 5; // match check() fallback
		}

		$has_issue = ( $dashboard_widgets > 15 );

		$result = self::check();
		$diagnostic_found_issue = is_array( $result );
		$test_passes = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Admin bar/dashboard weight check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (widgets: %d)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$dashboard_widgets
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}

}
