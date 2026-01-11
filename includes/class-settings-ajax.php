<?php
/**
 * Handle AJAX request to save settings.
 *
 * @return void
 */
function WPS_ajax_save_settings(): void {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'WPS_settings_form' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
	}

	// Parse the form data using wp-json encoded format.
	$data      = isset( $_POST['data'] ) ? sanitize_text_field( wp_unslash( $_POST['data'] ) ) : '';
	$form_data = json_decode( $data, true );

	if ( empty( $form_data ) || ! is_array( $form_data ) ) {
		wp_send_json_error( array( 'message' => 'Invalid form data' ) );
	}

	$group = isset( $form_data['group'] ) ? sanitize_key( $form_data['group'] ) : '';

	// Process based on settings group.
	switch ( $group ) {
		case 'module_registry':
			update_option( 'WPS_module_discovery_enabled', isset( $form_data['WPS_module_discovery_enabled'] ) ? 1 : 0 );
			update_option( 'WPS_module_discovery_frequency', sanitize_key( $form_data['WPS_module_discovery_frequency'] ?? 'on-demand' ) );
			break;

		case 'capabilities':
			update_option( 'WPS_capability_dashboard_role', sanitize_key( $form_data['WPS_capability_dashboard_role'] ?? 'manage_options' ) );
			$install_roles = isset( $form_data['WPS_capability_install_roles'] ) ? array_map( 'sanitize_key', (array) $form_data['WPS_capability_install_roles'] ) : array();
			update_option( 'WPS_capability_install_roles', $install_roles );
			$update_roles = isset( $form_data['WPS_capability_update_roles'] ) ? array_map( 'sanitize_key', (array) $form_data['WPS_capability_update_roles'] ) : array();
			update_option( 'WPS_capability_update_roles', $update_roles );
			break;

		case 'dashboard':
			update_option( 'WPS_dashboard_default_columns', absint( $form_data['WPS_dashboard_default_columns'] ?? 2 ) );
			$sticky_widgets = isset( $form_data['WPS_dashboard_sticky_widgets'] ) ? array_map( 'sanitize_key', (array) $form_data['WPS_dashboard_sticky_widgets'] ) : array();
			update_option( 'WPS_dashboard_sticky_widgets', $sticky_widgets );
			update_option( 'WPS_dashboard_widget_sorting', sanitize_key( $form_data['WPS_dashboard_widget_sorting'] ?? 'drag-order' ) );
			break;

		case 'license':
			$license_key = isset( $form_data['WPS_license_key'] ) ? sanitize_text_field( $form_data['WPS_license_key'] ) : '';
			update_option( 'WPS_license_key', $license_key );
			$auto_update = isset( $form_data['WPS_license_auto_update_types'] ) ? array_map( 'sanitize_key', (array) $form_data['WPS_license_auto_update_types'] ) : array();
			update_option( 'WPS_license_auto_update_types', $auto_update );
			update_option( 'WPS_license_update_channel', sanitize_key( $form_data['WPS_license_update_channel'] ?? 'stable' ) );
			break;

		case 'privacy':
			update_option( 'WPS_privacy_log_retention_days', absint( $form_data['WPS_privacy_log_retention_days'] ?? 90 ) );
			update_option( 'WPS_privacy_auto_delete_enabled', isset( $form_data['WPS_privacy_auto_delete_enabled'] ) ? 1 : 0 );
			update_option( 'WPS_privacy_auto_delete_days', absint( $form_data['WPS_privacy_auto_delete_days'] ?? 90 ) );
			update_option( 'WPS_privacy_audit_logging_level', sanitize_key( $form_data['WPS_privacy_audit_logging_level'] ?? 'standard' ) );
			update_option( 'WPS_privacy_export_format', sanitize_key( $form_data['WPS_privacy_export_format'] ?? 'json' ) );
			update_option( 'WPS_privacy_contributors_see_user_activity', isset( $form_data['WPS_privacy_contributors_see_user_activity'] ) ? 1 : 0 );
			update_option( 'WPS_privacy_editors_see_admin_activity', isset( $form_data['WPS_privacy_editors_see_admin_activity'] ) ? 1 : 0 );
			break;

		default:
			wp_send_json_error( array( 'message' => 'Invalid settings group' ) );
	}

	wp_send_json_success( array( 'message' => __( 'Settings saved successfully', 'plugin-wp-support-thisismyurl' ) ) );
}
add_action( 'wp_ajax_WPS_save_settings', 'WPS_ajax_save_settings' );
