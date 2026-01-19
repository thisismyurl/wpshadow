<?php
/**
 * Feature History Logging
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Log feature activity to history.
 *
 * @param string $feature_id The feature ID.
 * @param string $action The action performed (enabled, disabled, etc.).
 * @param string $details Optional additional details.
 * @return void
 */
function wpshadow_log_feature_activity( string $feature_id, string $action, string $details = '' ): void {
	if ( empty( $feature_id ) ) {
		return;
	}
	
	// Get the main logs option (used by Activity History widget)
	$all_logs = get_option( 'wpshadow_feature_logs', array() );
	
	if ( ! is_array( $all_logs ) ) {
		$all_logs = array();
	}
	
	// Initialize feature logs if not exists
	if ( ! isset( $all_logs[ $feature_id ] ) ) {
		$all_logs[ $feature_id ] = array();
	}
	
	if ( ! is_array( $all_logs[ $feature_id ] ) ) {
		$all_logs[ $feature_id ] = array();
	}
	
	// Add new entry
	$entry = array(
		'timestamp' => current_time( 'timestamp' ),
		'action'    => $action,
		'user_id'   => get_current_user_id(),
		'details'   => $details,
	);
	$all_logs[ $feature_id ][] = $entry;

	// If this is a child feature (feature_id contains '/'), also log under the parent feature id
	if ( strpos( $feature_id, '/' ) !== false ) {
		$parent_id = strtok( $feature_id, '/' );

		if ( ! isset( $all_logs[ $parent_id ] ) || ! is_array( $all_logs[ $parent_id ] ) ) {
			$all_logs[ $parent_id ] = array();
		}

		$parent_details = $details;
		if ( empty( $parent_details ) ) {
			// Derive a friendly child name from the child ID without forcing sentence case
			$parts    = explode( '/', $feature_id );
			$child_id = end( $parts );
			$tokens   = preg_split( '/[-_\s]+/', $child_id );
			$normalized_tokens = array_map( function( $token ) {
				$upper = strtoupper( $token );
				// Keep common abbreviations fully uppercased
				if ( in_array( $upper, array( 'CSS', 'JS', 'CDN', 'API', 'HTML', 'XML', 'SVG', 'JSON' ), true ) ) {
					return $upper;
				}
				// Preserve existing capitalization for mixed-case tokens
				if ( preg_match( '/[A-Z]/', $token ) ) {
					return $token;
				}
				// Capitalize first letter for standard words
				return ucfirst( strtolower( $token ) );
			}, $tokens );
			$parent_details = implode( ' ', array_filter( $normalized_tokens ) );
		}

		$parent_entry = $entry;
		$parent_entry['details'] = $parent_details;
		$all_logs[ $parent_id ][] = $parent_entry;
	}
	
	// Keep only last 100 entries per feature to prevent database bloat
	if ( count( $all_logs[ $feature_id ] ) > 100 ) {
		// Sort by timestamp descending
		usort( $all_logs[ $feature_id ], function( $a, $b ) {
			return ( $b['timestamp'] ?? 0 ) - ( $a['timestamp'] ?? 0 );
		} );
		// Keep only the 100 most recent
		$all_logs[ $feature_id ] = array_slice( $all_logs[ $feature_id ], 0, 100 );
	}
	
	// Update the main logs option
	update_option( 'wpshadow_feature_logs', $all_logs, false );
}
