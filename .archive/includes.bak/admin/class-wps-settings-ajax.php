<?php

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Settings_Ajax {

	public static function init(): void {
		add_action( 'wp_ajax_WPSHADOW_save_settings', array( __CLASS__, 'handle_save_settings' ) );
	}

	public static function handle_save_settings(): void {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpshadow_settings_form' ) ) {
			wp_send_json_error( array( 'message' => 'Your session expired. Please refresh and try again.' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You don\'t have permission to do that', 'wpshadow' ) ) );
		}

		$data      = \WPShadow\WPSHADOW_get_post_text( 'data' );
		$form_data = json_decode( $data, true );

		if ( empty( $form_data ) || ! is_array( $form_data ) ) {
			wp_send_json_error( array( 'message' => 'Please check your form and try again.' ) );
		}

		$group = isset( $form_data['group'] ) ? sanitize_key( $form_data['group'] ) : '';

		switch ( $group ) {
			case 'module_registry':
				self::save_module_registry_settings( $form_data );
				break;

			case 'capabilities':
				self::save_capabilities_settings( $form_data );
				break;

			case 'dashboard':
				self::save_dashboard_settings( $form_data );
				break;

			case 'license':
				self::save_license_settings( $form_data );
				break;

			case 'privacy':
				self::save_privacy_settings( $form_data );
				break;

			case 'database_cleanup':
				self::save_database_cleanup_settings( $form_data );
				break;

			default:
				wp_send_json_error( array( 'message' => 'Please select a settings group.' ) );
		}

		wp_send_json_success( array( 'message' => __( 'Settings saved successfully', 'wpshadow' ) ) );
	}

	private static function save_module_registry_settings( array $form_data ): void {
		update_option( 'wpshadow_module_discovery_enabled', isset( $form_data['wpshadow_module_discovery_enabled'] ) ? 1 : 0 );
		update_option( 'wpshadow_module_discovery_frequency', sanitize_key( $form_data['wpshadow_module_discovery_frequency'] ?? 'on-demand' ) );
	}

	private static function save_capabilities_settings( array $form_data ): void {
		update_option( 'wpshadow_capability_dashboard_role', sanitize_key( $form_data['wpshadow_capability_dashboard_role'] ?? 'manage_options' ) );
		$install_roles = isset( $form_data['wpshadow_capability_install_roles'] ) ? array_map( 'sanitize_key', (array) $form_data['wpshadow_capability_install_roles'] ) : array();
		update_option( 'wpshadow_capability_install_roles', $install_roles );
		$update_roles = isset( $form_data['wpshadow_capability_update_roles'] ) ? array_map( 'sanitize_key', (array) $form_data['wpshadow_capability_update_roles'] ) : array();
		update_option( 'wpshadow_capability_update_roles', $update_roles );
	}

	private static function save_dashboard_settings( array $form_data ): void {
		update_option( 'wpshadow_dashboard_default_columns', absint( $form_data['wpshadow_dashboard_default_columns'] ?? 2 ) );
		$sticky_widgets = isset( $form_data['wpshadow_dashboard_sticky_widgets'] ) ? array_map( 'sanitize_key', (array) $form_data['wpshadow_dashboard_sticky_widgets'] ) : array();
		update_option( 'wpshadow_dashboard_sticky_widgets', $sticky_widgets );
		update_option( 'wpshadow_dashboard_widget_sorting', sanitize_key( $form_data['wpshadow_dashboard_widget_sorting'] ?? 'drag-order' ) );
	}

	private static function save_license_settings( array $form_data ): void {

	}

	private static function save_privacy_settings( array $form_data ): void {
		update_option( 'wpshadow_privacy_log_retention_days', absint( $form_data['wpshadow_privacy_log_retention_days'] ?? 90 ) );
		update_option( 'wpshadow_privacy_auto_delete_enabled', isset( $form_data['wpshadow_privacy_auto_delete_enabled'] ) ? 1 : 0 );
		update_option( 'wpshadow_privacy_auto_delete_days', absint( $form_data['wpshadow_privacy_auto_delete_days'] ?? 90 ) );
		update_option( 'wpshadow_privacy_audit_logging_level', sanitize_key( $form_data['wpshadow_privacy_audit_logging_level'] ?? 'standard' ) );
		update_option( 'wpshadow_privacy_export_format', sanitize_key( $form_data['wpshadow_privacy_export_format'] ?? 'json' ) );
		update_option( 'wpshadow_privacy_contributors_see_user_activity', isset( $form_data['wpshadow_privacy_contributors_see_user_activity'] ) ? 1 : 0 );
		update_option( 'wpshadow_privacy_editors_see_admin_activity', isset( $form_data['wpshadow_privacy_editors_see_admin_activity'] ) ? 1 : 0 );
		update_option( 'wpshadow_diagnostic_logging_enabled', isset( $form_data['wpshadow_diagnostic_logging_enabled'] ) ? 1 : 0 );
	}

	private static function save_database_cleanup_settings( array $form_data ): void {

		$feature = \WPShadow\WPSHADOW_Feature_Registry::get_feature( 'database-cleanup' );
		if ( ! $feature ) {
			wp_send_json_error( array( 'message' => 'Database cleanup feature not found' ) );
		}

		$enabled = isset( $form_data['wpshadow_database_cleanup_enabled'] ) ? 1 : 0;
		$feature->update_setting( 'enabled', $enabled );

		$old_frequency = $feature->get_setting( 'cleanup_frequency', 'weekly' );
		$new_frequency = sanitize_key( $form_data['wpshadow_cleanup_frequency'] ?? 'weekly' );
		$feature->update_setting( 'cleanup_frequency', $new_frequency );

		$cleanup_options = array(
			'cleanup_revisions'     => isset( $form_data['wpshadow_cleanup_options']['cleanup_revisions'] ) ? 1 : 0,
			'cleanup_transients'    => isset( $form_data['wpshadow_cleanup_options']['cleanup_transients'] ) ? 1 : 0,
			'cleanup_spam'          => isset( $form_data['wpshadow_cleanup_options']['cleanup_spam'] ) ? 1 : 0,
			'cleanup_orphaned_meta' => isset( $form_data['wpshadow_cleanup_options']['cleanup_orphaned_meta'] ) ? 1 : 0,
			'cleanup_auto_drafts'   => isset( $form_data['wpshadow_cleanup_options']['cleanup_auto_drafts'] ) ? 1 : 0,
			'optimize_tables'       => isset( $form_data['wpshadow_cleanup_options']['optimize_tables'] ) ? 1 : 0,
			'keep_revisions'        => absint( $form_data['wpshadow_cleanup_options']['keep_revisions'] ?? 5 ),
		);
		$feature->update_setting( 'cleanup_options', $cleanup_options );

		if ( $old_frequency !== $new_frequency ) {

			$timestamp = wp_next_scheduled( 'wpshadow_database_cleanup' );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'wpshadow_database_cleanup' );
			}

			if ( $enabled ) {
				wp_schedule_event( time(), $new_frequency, 'wpshadow_database_cleanup' );
			}
		}
	}
}
