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
	
	$option_name = 'wpshadow_feature_history_' . $feature_id;
	$history = get_option( $option_name, array() );
	
	if ( ! is_array( $history ) ) {
		$history = array();
	}
	
	// Add new entry
	$history[] = array(
		'timestamp' => current_time( 'timestamp' ),
		'action'    => $action,
		'user_id'   => get_current_user_id(),
		'details'   => $details,
	);
	
	// Keep only last 50 entries to prevent database bloat
	if ( count( $history ) > 50 ) {
		// Sort by timestamp descending
		usort( $history, function( $a, $b ) {
			return ( $b['timestamp'] ?? 0 ) - ( $a['timestamp'] ?? 0 );
		} );
		// Keep only the 50 most recent
		$history = array_slice( $history, 0, 50 );
	}
	
	update_option( $option_name, $history, false );
}
