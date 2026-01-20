<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpshadow_log_feature_activity( string $feature_id, string $action, string $details = '' ): void {
	if ( empty( $feature_id ) ) {
		return;
	}

	$all_logs = get_option( 'wpshadow_feature_logs', array() );

	if ( ! is_array( $all_logs ) ) {
		$all_logs = array();
	}

	if ( ! isset( $all_logs[ $feature_id ] ) ) {
		$all_logs[ $feature_id ] = array();
	}

	if ( ! is_array( $all_logs[ $feature_id ] ) ) {
		$all_logs[ $feature_id ] = array();
	}

	$entry = array(
		'timestamp' => current_time( 'timestamp' ),
		'action'    => $action,
		'user_id'   => get_current_user_id(),
		'details'   => $details,
	);
	$all_logs[ $feature_id ][] = $entry;

	if ( strpos( $feature_id, '/' ) !== false ) {
		$parent_id = strtok( $feature_id, '/' );

		if ( ! isset( $all_logs[ $parent_id ] ) || ! is_array( $all_logs[ $parent_id ] ) ) {
			$all_logs[ $parent_id ] = array();
		}

		$parent_details = $details;
		if ( empty( $parent_details ) ) {

			$parts    = explode( '/', $feature_id );
			$child_id = end( $parts );
			$tokens   = preg_split( '/[-_\s]+/', $child_id );
			$normalized_tokens = array_map( function( $token ) {
				$upper = strtoupper( $token );

				if ( in_array( $upper, array( 'CSS', 'JS', 'CDN', 'API', 'HTML', 'XML', 'SVG', 'JSON' ), true ) ) {
					return $upper;
				}

				if ( preg_match( '/[A-Z]/', $token ) ) {
					return $token;
				}

				return ucfirst( strtolower( $token ) );
			}, $tokens );
			$parent_details = implode( ' ', array_filter( $normalized_tokens ) );
		}

		$parent_entry = $entry;
		$parent_entry['details'] = $parent_details;
		$all_logs[ $parent_id ][] = $parent_entry;
	}

	if ( count( $all_logs[ $feature_id ] ) > 100 ) {

		usort( $all_logs[ $feature_id ], function( $a, $b ) {
			return ( $b['timestamp'] ?? 0 ) - ( $a['timestamp'] ?? 0 );
		} );

		$all_logs[ $feature_id ] = array_slice( $all_logs[ $feature_id ], 0, 100 );
	}

	update_option( 'wpshadow_feature_logs', $all_logs, false );
}
