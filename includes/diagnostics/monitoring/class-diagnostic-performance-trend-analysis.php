<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Performance Trend Analysis Over Time (HISTORICAL-001)
 * 
 * Tracks performance metrics over time to identify degradation trends.
 * Philosophy: Show value (#9) - Catch performance regressions early.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Performance_Trend_Analysis extends Diagnostic_Base {
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Analyze performance trends
        $perf_history = get_transient('wpshadow_performance_trend');
        
        if ($perf_history && is_array($perf_history) && count($perf_history) > 3) {
            $latest = end($perf_history);
            $oldest = reset($perf_history);
            
            $degradation = $latest - $oldest;
            
            // If performance has degraded by more than 500ms over time
            if ($degradation > 500) {
                return array(
                    'id' => 'performance-trend-analysis',
                    'title' => sprintf(__('Performance Degradation (+%dms)', 'wpshadow'), $degradation),
                    'description' => __('Site performance is declining over time. Identify and remove recent plugins/updates causing slowdown.', 'wpshadow'),
                    'severity' => 'medium',
                    'category' => 'monitoring',
                    'kb_link' => 'https://wpshadow.com/kb/performance-trend-monitoring/',
                    'training_link' => 'https://wpshadow.com/training/degradation-analysis/',
                    'auto_fixable' => false,
                    'threat_level' => 50,
                );
            }
        }
        return null;
	}
}
