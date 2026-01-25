<?php
/**
 * Treatment Base Class
 *
 * Abstract base class for treatments to eliminate code duplication.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Treatment Base Class
 *
 * Provides common functionality for all treatments, including
 * capability checking for multisite environments.
 */
abstract class Treatment_Base implements Treatment_Interface {
	/**
	 * Check if this treatment can be applied in the current environment.
	 *
	 * Handles both single-site and multisite capability checks.
	 * Treatments can override this if they need custom logic.
	 *
	 * @return bool True if treatment can be applied.
	 */
	public static function can_apply() {
		// Multisite network admin requires network options capability
		if ( is_multisite() && is_network_admin() ) {
			return current_user_can( 'manage_network_options' );
		}
		
		// Single site or multisite sub-site requires options capability
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * Must be implemented by child classes.
	 *
	 * @return string Finding ID.
	 */
	abstract public static function get_finding_id();

	/**
	 * Apply the treatment to fix the finding.
	 *
	 * Must be implemented by child classes.
	 *
	 * @return array Result array with 'success' and 'message' keys.
	 */
	abstract public static function apply();

	/**
	 * Execute treatment with hooks.
	 *
	 * Wraps apply() with before/after actions for extensibility.
	 *
	 * @param bool $dry_run Whether to run in dry-run mode (check only, don't apply).
	 * @return array Result array.
	 */
	public static function execute( $dry_run = false ) {
		$class = get_called_class();
		$finding_id = static::get_finding_id();

		/**
		 * Fires before a treatment is applied.
		 *
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 * @param bool   $dry_run    Whether this is a dry run.
		 */
		do_action( 'wpshadow_before_treatment_apply', $class, $finding_id, $dry_run );

		if ( $dry_run ) {
			// In dry-run mode, check if treatment can be applied but don't execute
			$can_apply = static::can_apply();
			$result = array(
				'success'  => $can_apply,
				'message'  => $can_apply 
					? 'Treatment can be applied (dry run - no changes made)' 
					: 'Treatment cannot be applied at this time',
				'dry_run'  => true,
				'would_apply' => $can_apply,
			);
		} else {
			$result = static::apply();
			
			// Clear findings cache after treatment is applied
			if ( function_exists( 'wpshadow_clear_findings_cache' ) ) {
				wpshadow_clear_findings_cache();
			}
			
			// Record in rollback log if successful
			if ( ! empty( $result['success'] ) ) {
				self::record_rollback_info( $finding_id, $class );
			}
		}

		/**
		 * Fires after a treatment is applied.
		 *
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 * @param array  $result     Treatment result.
		 */
		do_action( 'wpshadow_after_treatment_apply', $class, $finding_id, $result );

		/**
		 * Filter treatment result.
		 *
		 * @param array  $result     Result array.
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 */
		return apply_filters( 'wpshadow_treatment_result', $result, $class, $finding_id );
	}

	/**
	 * Record treatment application for rollback tracking.
	 *
	 * @param string $finding_id Finding identifier.
	 * @param string $class      Treatment class name.
	 */
	private static function record_rollback_info( $finding_id, $class ) {
		$rollback_log = get_option( 'wpshadow_rollback_log', array() );
		
		$rollback_log[] = array(
			'finding_id' => $finding_id,
			'class'      => $class,
			'timestamp'  => time(),
			'user_id'    => get_current_user_id(),
		);
		
		// Keep only last 100 entries
		if ( count( $rollback_log ) > 100 ) {
			$rollback_log = array_slice( $rollback_log, -100 );
		}
		
		update_option( 'wpshadow_rollback_log', $rollback_log );
	}

	/**
	 * Get rollback history.
	 *
	 * @return array Array of rollback log entries.
	 */
	public static function get_rollback_history() {
		return get_option( 'wpshadow_rollback_log', array() );
	}

	/**
	 * Undo the treatment.
	 *
	 * Must be implemented by child classes.
	 *
	 * @return array Result array with 'success' and 'message' keys.
	 */
	abstract public static function undo();

	/**
	 * Execute undo with hooks.
	 *
	 * Wraps undo() with before/after actions for extensibility.
	 *
	 * @return array Result array.
	 */
	public static function execute_undo() {
		$class = get_called_class();
		
		// Get finding_id if method exists, otherwise use class name.
		$finding_id = method_exists( $class, 'get_finding_id' ) ? static::get_finding_id() : $class;

		/**
		 * Fires before a treatment is undone.
		 *
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 */
		do_action( 'wpshadow_before_treatment_undo', $class, $finding_id );

		$result = static::undo();

		// Clear findings cache after undo
		if ( ! empty( $result['success'] ) ) {
			if ( function_exists( 'wpshadow_clear_findings_cache' ) ) {
				wpshadow_clear_findings_cache();
			}
		}

		/**
		 * Fires after a treatment is undone.
		 *
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 * @param array  $result     Undo result.
		 */
		do_action( 'wpshadow_after_treatment_undo', $class, $finding_id, $result );

		/**
		 * Filter treatment undo result.
		 *
		 * @param array  $result     Result array.
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 */
		return apply_filters( 'wpshadow_treatment_undo_result', $result, $class, $finding_id );
	}
}
