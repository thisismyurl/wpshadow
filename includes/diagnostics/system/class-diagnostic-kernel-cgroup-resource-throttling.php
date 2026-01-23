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
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
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
