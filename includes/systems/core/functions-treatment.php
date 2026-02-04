<?php
/**
 * Treatment Helper Functions
 *
 * Global convenience functions for treatment operations used throughout the plugin.
 * These wrap Treatment_Registry and Treatment_Base functionality in simpler,
 * more accessible functions.
 *
 * **When to Use These Functions:**
 * - `wpshadow_attempt_autofix()` - Manually trigger a specific treatment
 * - `wpshadow_get_treatment()` - Load a treatment instance for inspection
 * - `wpshadow_is_treatment_enabled()` - Check if user has disabled this fix
 * - `wpshadow_can_apply_treatment()` - Permission check before UI display
 *
 * **Real-World Usage:**
 * ```php
 * // In dashboard: offer fix button only if treatment available
 * if ( wpshadow_can_apply_treatment( 'database-cleanup' ) ) {
 *     // Show "Fix Now" button
 * }
 *
 * // When user clicks button: apply the fix
 * $result = wpshadow_attempt_autofix( 'database-cleanup', $dry_run = true );
 * // Show results to user...
 * ```
 *
 * **Philosophy Alignment:**
 * - #1 (Helpful Neighbor): Simple functions hide complexity
 * - #7 (Ridiculously Good): Low barrier to use treatments in custom code
 *
 * @package WPShadow
 * @since 1.6030.2148
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attempt to automatically fix a finding.
 *
 * Convenience wrapper around Treatment_Registry that applies a treatment by
 * finding ID. Handles permission checks, backup creation, and logging.
 *
 * **When NOT to Use:**
 * - Inside a loop (use Treatment_Registry directly for batch operations)
 * - Without user permission check first (always verify can_apply_treatment)
 * - Without backup being available (check backup_enabled setting)
 *
 * **Dry-Run Mode:**
 * Pass $dry_run=true to preview changes without persisting:
 * - Shows what would be modified
 * - Returns impact metrics
 * - No files/database actually changed
 * - Perfect for showing users before asking "ready to proceed?"
 *
 * **Result Format:**
 * Always returns array with:
 * - `success` (bool): Whether treatment applied successfully
 * - `message` (string): Human-readable result or error
 * - `data` (array): Optional - treatment-specific results
 *
 * @param string $finding_id Finding identifier (e.g., 'database-cleanup').
 * @param bool   $dry_run    Optional. Whether to simulate without persisting. Default false.
 * @return array {
 *     Result of treatment application.
 *
 *     @type bool   $success Whether treatment succeeded.
 *     @type string $message Human-readable result message.
 *     @type array  $data    Optional. Treatment-specific results.
 * }
 */
function wpshadow_attempt_autofix( $finding_id, $dry_run = false ) {
	// Validate finding_id
	if ( empty( $finding_id ) || ! is_string( $finding_id ) ) {
		return array(
			'success' => false,
			'message' => 'Invalid finding ID provided.',
		);
	}

	// Check if Treatment_Registry is available
	if ( ! class_exists( 'WPShadow\Treatments\Treatment_Registry' ) ) {
		return array(
			'success' => false,
			'message' => 'Treatment system is not available.',
		);
	}

	// Apply the treatment through the registry
	return \WPShadow\Treatments\Treatment_Registry::apply_treatment( $finding_id, $dry_run );
}

/**
 * Undo/rollback a previously applied treatment.
 *
 * @param string $finding_id Finding identifier.
 * @return array Result array with 'success' and 'message' keys.
 */
function wpshadow_rollback_fix( $finding_id ) {
	// Validate finding_id
	if ( empty( $finding_id ) || ! is_string( $finding_id ) ) {
		return array(
			'success' => false,
			'message' => 'Invalid finding ID provided.',
		);
	}

	// Check if Treatment_Registry is available
	if ( ! class_exists( 'WPShadow\Treatments\Treatment_Registry' ) ) {
		return array(
			'success' => false,
			'message' => 'Treatment system is not available.',
		);
	}

	// Undo the treatment through the registry
	return \WPShadow\Treatments\Treatment_Registry::undo_treatment( $finding_id );
}

/**
 * Get the rollback history.
 *
 * @return array Array of rollback log entries.
 */
function wpshadow_get_rollback_history() {
	if ( ! class_exists( 'WPShadow\Core\Treatment_Base' ) ) {
		return array();
	}

	return \WPShadow\Core\Treatment_Base::get_rollback_history();
}

/**
 * Check if a treatment can be rolled back.
 *
 * @param string $finding_id Finding identifier.
 * @return bool True if the treatment can be rolled back.
 */
function wpshadow_can_rollback( $finding_id ) {
	if ( ! class_exists( 'WPShadow\Treatments\Treatment_Registry' ) ) {
		return false;
	}

	$treatment = \WPShadow\Treatments\Treatment_Registry::get_treatment( $finding_id );

	if ( ! $treatment ) {
		return false;
	}

	return method_exists( $treatment, 'undo' );
}
