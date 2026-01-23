<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Long Task Clustering Analysis (FE-320)
 *
 * Groups TBT bursts to find root causes and stacks.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_LongTaskClustering extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		$cluster_count = (int) get_transient('wpshadow_long_task_cluster_count');
		$worst_cluster_ms = (int) get_transient('wpshadow_long_task_worst_cluster_ms');

		if ($cluster_count > 3 || $worst_cluster_ms > 200) {
			return array(
				'id' => 'long-task-clustering',
				'title' => sprintf(__('Long task clusters detected (%d)', 'wpshadow'), max($cluster_count, 1)),
				'description' => __('Multiple long tasks are grouped together, increasing Total Blocking Time. Break up heavy JS work, defer non-critical scripts, and schedule work in rAF.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'system',
				'kb_link' => 'https://wpshadow.com/kb/long-task-clusters/',
				'training_link' => 'https://wpshadow.com/training/javascript-performance/',
				'auto_fixable' => false,
				'threat_level' => 50,
			);
		}

		return null;
	}
    
}