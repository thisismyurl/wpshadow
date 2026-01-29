<?php
/**
 * Workflow Trigger Validation Diagnostic
 *
 * Verifies workflows have valid triggers and hooks are registered.
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
 * Workflow Trigger Validation Class
 *
 * Identifies orphaned workflows with invalid triggers.
 * Ensures automation executes as intended.
 *
 * @since 1.5029.1150
 */
class Diagnostic_Workflow_Triggers extends Diagnostic_Base {

	protected static $slug        = 'workflow-trigger-validation';
	protected static $title       = 'Workflow Trigger Validation';
	protected static $description = 'Validates workflow triggers and hook registration';
	protected static $family      = 'workflows';

	public static function check() {
		$cache_key = 'wpshadow_workflow_triggers_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get workflows from options table using WordPress API (NO $wpdb).
		$workflows = get_option( 'wpshadow_workflows', array() );

		if ( empty( $workflows ) || ! is_array( $workflows ) ) {
			set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
			return null;
		}

		$orphaned_workflows = array();
		global $wp_filter;

		foreach ( $workflows as $workflow_id => $workflow ) {
			$trigger = $workflow['trigger'] ?? '';
			if ( empty( $trigger ) ) {
				$orphaned_workflows[] = array(
					'id'     => $workflow_id,
					'name'   => $workflow['name'] ?? 'Unnamed',
					'reason' => 'No trigger defined',
				);
				continue;
			}

			// Check if trigger hook is registered.
			if ( ! isset( $wp_filter[ $trigger ] ) || empty( $wp_filter[ $trigger ]->callbacks ) ) {
				$orphaned_workflows[] = array(
					'id'      => $workflow_id,
					'name'    => $workflow['name'] ?? 'Unnamed',
					'trigger' => $trigger,
					'reason'  => 'Hook not registered',
				);
			}
		}

		if ( count( $orphaned_workflows ) > 0 ) {
			$threat_level = 30;
			if ( count( $orphaned_workflows ) > 3 ) {
				$threat_level = 45;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of orphaned workflows */
					__( '%d workflows have invalid triggers and will never execute.', 'wpshadow' ),
					count( $orphaned_workflows )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/workflows-trigger-validation',
				'data'         => array(
					'orphaned_workflows' => $orphaned_workflows,
					'total_workflows'    => count( $workflows ),
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
		return null;
	}
}
