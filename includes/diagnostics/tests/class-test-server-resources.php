<?php

/**
 * WPShadow System Diagnostic Test: Server Resources
 *
 * Tests available disk space, load average, available memory.
 *
 * Testable via: disk_free_space(), sys_getloadavg(), memory monitoring
 * Can be requested by Guardian: "test-disk-space", "test-load-average", etc.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Proactive resource monitoring
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Server Resources (Disk, Load, Memory)
 *
 * Main diagnostic for system resource monitoring.
 * Can request specific resource tests via Guardian.
 *
 * @verified Not yet tested
 */
class Test_Server_Resources extends Diagnostic_Base
{

	protected static $slug = 'server-resources';
	protected static $title = 'Server Resource Status';
	protected static $description = 'Monitors disk space, load average, and memory availability.';

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		$issues = array();

		// Check disk space
		$upload_dir = wp_upload_dir();
		$free_space = @disk_free_space($upload_dir['basedir']);
		$total_space = @disk_total_space($upload_dir['basedir']);

		if ($free_space !== false && $total_space !== false) {
			$used_space = $total_space - $free_space;
			$usage_percent = ($used_space / $total_space) * 100;

			if ($usage_percent >= 80) {
				$severity = 'medium';
				$threat = 60;
				if ($usage_percent >= 95) {
					$severity = 'critical';
					$threat = 100;
				} elseif ($usage_percent >= 90) {
					$severity = 'high';
					$threat = 80;
				}

				$issues[] = array(
					'type' => 'disk-space',
					'severity' => $severity,
					'threat' => $threat,
					'data' => array(
						'usage_percent' => round($usage_percent, 1),
						'free_space' => $free_space,
						'total_space' => $total_space,
					),
				);
			}
		}

		// Check load average
		if (function_exists('sys_getloadavg')) {
			$load = sys_getloadavg();
			$one_minute = (float) $load[0];
			$cpu_count = self::get_cpu_count();
			$threshold = $cpu_count * 1.5;

			if ($one_minute > $threshold) {
				$issues[] = array(
					'type' => 'load-average',
					'severity' => $one_minute > ($cpu_count * 3) ? 'critical' : 'high',
					'threat' => $one_minute > ($cpu_count * 3) ? 80 : 50,
					'data' => array(
						'load_average' => round($one_minute, 2),
						'cpu_count' => $cpu_count,
						'threshold' => round($threshold, 2),
					),
				);
			}
		}

		if (! empty($issues)) {
			$primary_issue = reset($issues);
			return array(
				'id'            => static::$slug . '-' . $primary_issue['type'],
				'title'         => 'Server Resource Warning',
				'description'   => 'One or more server resources are running low. Investigate immediately.',
				'color'         => '#ff9800',
				'bg_color'      => '#fff3e0',
				'kb_link'       => 'https://wpshadow.com/kb/server-resources/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=server-resources',
				'training_link' => 'https://wpshadow.com/training/server-health/',
				'auto_fixable'  => false,
				'threat_level'  => $primary_issue['threat'],
				'module'        => 'System',
				'priority'      => 2,
				'meta'          => array(
					'issues' => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Helper: Get CPU core count
	 */
	private static function get_cpu_count(): int
	{
		if (function_exists('wp_cache_get')) {
			$cached = wp_cache_get('wpshadow_cpu_count');
			if ($cached !== false) {
				return (int) $cached;
			}
		}

		if (function_exists('shell_exec')) {
			$count = (int) @shell_exec('nproc 2>/dev/null');
			if ($count > 0) {
				if (function_exists('wp_cache_set')) {
					wp_cache_set('wpshadow_cpu_count', $count, '', 3600);
				}
				return $count;
			}
		}

		return 2; // Safe default
	}

	/**
	 * Helper: Format bytes to human-readable
	 */
	private static function format_bytes($bytes): string
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= (1 << (10 * $pow));

		return round($bytes, 2) . ' ' . $units[$pow];
	}

	/**
	 * Guardian can request: "test-disk-space"
	 * Checks: Disk usage < 80%
	 */
	public static function test_disk_space(): array
	{
		$upload_dir = wp_upload_dir();
		$free_space = @disk_free_space($upload_dir['basedir']);
		$total_space = @disk_total_space($upload_dir['basedir']);

		if ($free_space === false || $total_space === false) {
			return array(
				'passed'  => false,
				'message' => '✗ Unable to determine disk space',
				'data'    => array(
					'path' => $upload_dir['basedir'],
					'error' => 'disk_free_space() or disk_total_space() not available',
				),
			);
		}

		$used_space = $total_space - $free_space;
		$usage_percent = ($used_space / $total_space) * 100;
		$passed = $usage_percent < 80;

		return array(
			'passed'  => $passed,
			'message' => $passed
				? "✓ Disk usage at " . round($usage_percent, 1) . "% (healthy)"
				: "✗ Disk usage at " . round($usage_percent, 1) . "% (critical)",
			'data'    => array(
				'usage_percent' => round($usage_percent, 1),
				'free_space' => $free_space,
				'free_space_formatted' => self::format_bytes($free_space),
				'total_space' => $total_space,
				'total_space_formatted' => self::format_bytes($total_space),
				'used_space' => $used_space,
				'used_space_formatted' => self::format_bytes($used_space),
			),
		);
	}

	/**
	 * Guardian can request: "test-load-average"
	 * Checks: Load average < 1.5x CPU count
	 */
	public static function test_load_average(): array
	{
		if (! function_exists('sys_getloadavg')) {
			return array(
				'passed'  => false,
				'message' => '⚠ sys_getloadavg() not available on this system',
				'data'    => array(
					'available' => false,
					'note' => 'Typical on Windows servers',
				),
			);
		}

		$load = sys_getloadavg();
		$one_minute = (float) $load[0];
		$five_minutes = (float) $load[1];
		$fifteen_minutes = (float) $load[2];
		$cpu_count = self::get_cpu_count();
		$threshold = $cpu_count * 1.5;

		$passed = $one_minute < $threshold;

		return array(
			'passed'  => $passed,
			'message' => $passed
				? "✓ Server load is normal ({$one_minute})"
				: "✗ Server load is high ({$one_minute}, threshold {$threshold})",
			'data'    => array(
				'load_1_min' => round($one_minute, 2),
				'load_5_min' => round($five_minutes, 2),
				'load_15_min' => round($fifteen_minutes, 2),
				'cpu_count' => $cpu_count,
				'threshold' => round($threshold, 2),
				'status' => $passed ? 'normal' : 'high',
			),
		);
	}

	/**
	 * Guardian can request: "test-memory-availability"
	 * Returns current memory usage
	 */
	public static function test_memory_availability(): array
	{
		$memory_used = memory_get_usage(true);
		$memory_peak = memory_get_peak_usage(true);
		$memory_limit = self::get_php_memory_limit();

		$percent_used = $memory_limit > 0 ? ($memory_used / $memory_limit * 100) : 0;
		$percent_peak = $memory_limit > 0 ? ($memory_peak / $memory_limit * 100) : 0;

		return array(
			'passed'  => $percent_used < 80,
			'message' => "Current memory usage: " . round($percent_used, 1) . "% of limit",
			'data'    => array(
				'memory_used' => $memory_used,
				'memory_used_formatted' => self::format_bytes($memory_used),
				'memory_peak' => $memory_peak,
				'memory_peak_formatted' => self::format_bytes($memory_peak),
				'memory_limit' => $memory_limit,
				'memory_limit_formatted' => self::format_bytes($memory_limit),
				'percent_used' => round($percent_used, 1),
				'percent_peak' => round($percent_peak, 1),
			),
		);
	}

	/**
	 * Guardian can request: "test-server-resources-all"
	 * Returns comprehensive resource snapshot
	 */
	public static function test_server_resources_all(): array
	{
		$disk = self::test_disk_space();
		$load = self::test_load_average();
		$memory = self::test_memory_availability();

		return array(
			'passed'  => $disk['passed'] && $load['passed'] && $memory['passed'],
			'message' => 'Complete server resource snapshot',
			'data'    => array(
				'disk_space' => $disk,
				'load_average' => $load,
				'memory' => $memory,
			),
		);
	}

	/**
	 * Helper: Get PHP memory limit in bytes
	 */
	private static function get_php_memory_limit(): int
	{
		$limit = ini_get('memory_limit');

		if ($limit === '-1' || strtoupper($limit) === 'UNLIMITED') {
			return PHP_INT_MAX;
		}

		$matches = array();
		if (preg_match('/^(\d+)\s*([KMG])B?$/i', $limit, $matches)) {
			$size = (int) $matches[1];
			$unit = strtoupper($matches[2]);
			return $size * array('K' => 1024, 'M' => 1024 ** 2, 'G' => 1024 ** 3)[$unit];
		}

		return (int) $limit;
	}
}
