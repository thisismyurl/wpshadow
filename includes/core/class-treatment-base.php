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
	 * @return array Result array.
	 */
	public static function execute() {
		$class = get_called_class();
		$finding_id = static::get_finding_id();

		/**
		 * Fires before a treatment is applied.
		 *
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 */
		do_action( 'wpshadow_before_treatment_apply', $class, $finding_id );

		$result = static::apply();

		// Clear findings cache after treatment is applied
		if ( function_exists( 'wpshadow_clear_findings_cache' ) ) {
			wpshadow_clear_findings_cache();
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
	 * Undo the treatment.
	 *
	 * Must be implemented by child classes.
	 *
	 * @return array Result array with 'success' and 'message' keys.
	 */
	abstract public static function undo();
}
