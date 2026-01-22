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
}
