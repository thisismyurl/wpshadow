<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Cron-Triggered Database Load Spikes (DB-312)
 *
 * Identifies scheduled tasks that spike DB load.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_CronDbLoadSpikes extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		$spike_events = get_transient('wpshadow_cron_db_spike_events');
		$spike_events = is_array($spike_events) ? $spike_events : array();
		$spike_count = count($spike_events);

		if ($spike_count > 0) {
			$latest = end($spike_events);
			return array(
				'id' => 'cron-db-load-spikes',
				'title' => sprintf(__('Cron jobs spiking DB load (%d recent)', 'wpshadow'), $spike_count),
				'description' => __('Scheduled tasks are causing database load spikes. Stagger heavy jobs, reduce query volume, or offload to external cron.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'system',
				'kb_link' => 'https://wpshadow.com/kb/cron-db-load-spikes/',
				'training_link' => 'https://wpshadow.com/training/cron-optimization/',
				'auto_fixable' => false,
				'threat_level' => 50,
				'latest_spike' => $latest,
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
