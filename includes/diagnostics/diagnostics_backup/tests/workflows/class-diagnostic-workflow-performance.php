<?php
/**
 * Workflow Execution Performance Diagnostic
 *
 * Monitors workflow completion times and identifies bottlenecks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1150
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Workflow Execution Performance Class
 *
 * Tracks workflow execution times to identify slow operations.
 * Slow workflows can miss triggers and cause automation failures.
 *
 * @since 1.5029.1150
 */
class Diagnostic_Workflow_Performance extends Diagnostic_Base {

	protected static $slug        = 'workflow-execution-performance';
	protected static $title       = 'Workflow Execution Performance';
	protected static $description = 'Monitors workflow execution times and bottlenecks';
	protected static $family      = 'workflows';

	public static function check() {
		$cache_key = 'wpshadow_workflow_performance_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get workflow execution logs using WordPress API (NO $wpdb).
		$execution_log = get_option( 'wpshadow_workflow_execution_log', array() );

		if ( empty( $execution_log ) || ! is_array( $execution_log ) ) {
			set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
			return null;
		}

		$slow_workflows = array();
		$timeout_workflows = array();

		foreach ( $execution_log as $entry ) {
			$execution_time = $entry['execution_time'] ?? 0;
			$workflow_name  = $entry['workflow_name'] ?? 'Unknown';

			// Flag workflows > 5 seconds.
			if ( $execution_time > 5000 ) {
				$slow_workflows[] = array(
					'name'           => $workflow_name,
					'execution_time' => $execution_time,
					'timestamp'      => $entry['timestamp'] ?? 0,
				);
			}

			// Flag timeouts.
			if ( isset( $entry['timeout'] ) && $entry['timeout'] ) {
				$timeout_workflows[] = array(
					'name'      => $workflow_name,
					'timestamp' => $entry['timestamp'] ?? 0,
				);
			}
		}

		if ( count( $slow_workflows ) > 0 || count( $timeout_workflows ) > 0 ) {
			$threat_level = 20;
			if ( count( $timeout_workflows ) > 0 ) {
				$threat_level = 45;
			} elseif ( count( $slow_workflows ) > 5 ) {
				$threat_level = 35;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: slow workflows, 2: timeout workflows */
					__( '%1$d slow workflows, %2$d timeouts detected. Performance issues may cause automation failures.', 'wpshadow' ),
					count( $slow_workflows ),
					count( $timeout_workflows )
				),
				'severity'     => $threat_level > 35 ? 'medium' : 'low',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/workflows-execution-performance',
				'data'         => array(
					'slow_workflows'    => array_slice( $slow_workflows, 0, 10 ),
					'timeout_workflows' => $timeout_workflows,
					'total_executions'  => count( $execution_log ),
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
		return null;
	}
}
