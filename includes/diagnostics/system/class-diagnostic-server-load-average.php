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
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}
