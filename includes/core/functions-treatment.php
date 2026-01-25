<?php
/**
 * Treatment Helper Functions
 *
 * Global helper functions for treatment operations.
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attempt to automatically fix a finding.
 *
 * This is a convenience wrapper around Treatment_Registry::apply_treatment()
 * that is used throughout the codebase.
 *
 * @param string $finding_id Finding identifier.
 * @param bool   $dry_run    Whether to run in dry-run mode (default: false).
 * @return array Result array with 'success' and 'message' keys.
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
