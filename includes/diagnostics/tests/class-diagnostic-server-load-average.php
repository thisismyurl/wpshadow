<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Server Load Average Monitoring (SERVER-014)
 * 
 * Tracks system load average to detect resource exhaustion.
 * Philosophy: Show value (#9) - Proactive alert before site crashes.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Server_Load_Average extends Diagnostic_Base {
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		if (!function_exists('sys_getloadavg')) {
			return null;
		}

		$load = sys_getloadavg();
		$one_minute = isset($load[0]) ? (float) $load[0] : 0.0;
		$cpu_count = (int) (function_exists('wp_cache_get') ? wp_cache_get('wpshadow_cpu_count') : 0);
		if ($cpu_count <= 0) {
			$cpu_count = (int) (function_exists('shell_exec') ? (int) shell_exec('nproc 2>/dev/null') : 0);
		}
		if ($cpu_count <= 0) {
			$cpu_count = 2; // sensible default
		}

		$threshold = max(1.0, $cpu_count * 1.5);
		if ($one_minute > $threshold) {
			return array(
				'id' => 'server-load-average',
				'title' => sprintf(__('High server load average (%.1f)', 'wpshadow'), $one_minute),
				'description' => __('Server load average exceeds available CPU. Investigate runaway processes, heavy cron jobs, or move to higher-capacity hosting.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'system',
				'kb_link' => 'https://wpshadow.com/kb/server-load-average/',
				'training_link' => 'https://wpshadow.com/training/server-health/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}

		return null;
	}
    


	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Server Load Average
	 * Slug: -server-load-average
	 * File: class-diagnostic-server-load-average.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Server Load Average
	 * Slug: -server-load-average
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
	public static function test_live__server_load_average(): array {
		if ( ! function_exists( 'sys_getloadavg' ) ) {
			return array(
				'passed'  => true,
				'message' => 'sys_getloadavg unavailable; diagnostic correctly treats as pass (no data)',
			);
		}

		$load       = sys_getloadavg();
		$one_minute = isset( $load[0] ) ? (float) $load[0] : 0.0;
		$cpu_count  = (int) ( function_exists( 'wp_cache_get' ) ? wp_cache_get( 'wpshadow_cpu_count' ) : 0 );
		if ( $cpu_count <= 0 ) {
			$cpu_count = (int) ( function_exists( 'shell_exec' ) ? (int) shell_exec( 'nproc 2>/dev/null' ) : 0 );
		}
		if ( $cpu_count <= 0 ) {
			$cpu_count = 2;
		}

		$threshold         = max( 1.0, $cpu_count * 1.5 );
		$expected_issue    = ( $one_minute > $threshold );
		$diagnostic_result = self::check();
		$diagnostic_has_issue = ( null !== $diagnostic_result );
		$test_passes       = ( $expected_issue === $diagnostic_has_issue );

		$message = sprintf(
			'1m load: %.2f, CPUs: %d, threshold: %.2f. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$one_minute,
			$cpu_count,
			$threshold,
			$expected_issue ? 'FIND' : 'NOT find',
			$diagnostic_has_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}

}
