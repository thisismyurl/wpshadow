<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Kernel/Cgroup Resource Throttling (SYSTEM-367)
 *
 * Identifies CPU/memory/io cgroup throttling in containers/shared hosts.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_KernelCgroupResourceThrottling extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		$throttled = 0;
		$stat_file = '/sys/fs/cgroup/cpu.stat';
		if (file_exists($stat_file) && is_readable($stat_file)) {
			$contents = file_get_contents($stat_file);
			if (is_string($contents)) {
				if (preg_match('/nr_throttled\s+(\d+)/', $contents, $matches)) {
					$throttled = (int) $matches[1];
				}
			}
		}

		if ($throttled > 0) {
			return array(
				'id' => 'kernel-cgroup-resource-throttling',
				'title' => __('CPU cgroup throttling detected', 'wpshadow'),
				'description' => __('The container/VM CPU is being throttled by the host. Reduce concurrent work, optimize cron jobs, or upgrade CPU quota.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'system',
				'kb_link' => 'https://wpshadow.com/kb/cgroup-throttling/',
				'training_link' => 'https://wpshadow.com/training/container-performance/',
				'auto_fixable' => false,
				'threat_level' => 55,
				'throttled_events' => $throttled,
			);
		}

		return null;
	}
    }
