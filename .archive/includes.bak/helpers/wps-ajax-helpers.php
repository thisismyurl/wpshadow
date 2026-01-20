<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpshadow_ajax_success( array $data = array() ): void {
	wp_send_json_success( $data );
}

function wpshadow_ajax_error( string $message, int $code = 400 ): void {
	wp_send_json_error( array( 'message' => $message ), $code );
}

function wpshadow_ajax_permission_denied(): void {
	WPSHADOW_ajax_error( __( 'Insufficient permissions', 'wpshadow' ), 403 );
}

function wpshadow_ajax_auth_required(): void {
	WPSHADOW_ajax_error( __( 'You must be logged in to perform this action.', 'wpshadow' ), 401 );
}

function wpshadow_ajax_invalid_request( string $field = '' ): void {
	$message = $field
		? sprintf( __( 'Please fill in the required field: %s', 'wpshadow' ), $field )
		: __( 'That request didn\'t work', 'wpshadow' );

	WPSHADOW_ajax_error( $message, 400 );
}

function wpshadow_ajax_not_found( string $resource = '' ): void {
	$message = $resource
		? sprintf( __( '%s not found', 'wpshadow' ), $resource )
		: __( 'Resource not found', 'wpshadow' );

	WPSHADOW_ajax_error( $message, 404 );
}

function wpshadow_verify_ajax_request( string $nonce_action, string $capability = 'manage_options', string $nonce_key = 'nonce' ): void {
	check_ajax_referer( $nonce_action, $nonce_key );

	if ( ! current_user_can( $capability ) ) {
		WPSHADOW_ajax_permission_denied();
	}
}

function wpshadow_verify_admin_request( string $nonce_action, string $nonce_key, string $capability = 'manage_options' ): void {
	$nonce = isset( $_POST[ $nonce_key ] ) ? wp_unslash( $_POST[ $nonce_key ] ) : ( isset( $_GET[ $nonce_key ] ) ? wp_unslash( $_GET[ $nonce_key ] ) : '' );

	if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, $nonce_action ) ) {
		wp_die( esc_html__( 'Security check failed. Please try again.', 'wpshadow' ) );
	}

	if ( ! current_user_can( $capability ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'wpshadow' ) );
	}
}

function wpshadow_verify_rest_request( \WP_REST_Request $request, string $capability = 'manage_options' ) {
	if ( ! current_user_can( $capability ) ) {
		return new \WP_Error(
			'rest_forbidden',
			__( 'You do not have sufficient permissions to perform this action.', 'wpshadow' ),
			array( 'status' => 403 )
		);
	}

	return true;
}
