<?php
/**
 * Settings AJAX Handler
 *
 * Handles AJAX requests for saving plugin settings.
 *
 * @package    WP_Support
 * @subpackage Admin
 * @since      1.2601.73002
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Settings_Ajax Class
 *
 * Manages AJAX operations for settings forms across the plugin.
 */
class WPSHADOW_Settings_Ajax {

	/**
	 * Initialize the settings AJAX handler.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_ajax_WPSHADOW_save_settings', array( __CLASS__, 'handle_save_settings' ) );
	}

	/**
	 * Handle AJAX request to save settings.
	 *
	 * @return void
	 */
	public static function handle_save_settings(): void {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpshadow_settings_form' ) ) {
			wp_send_json_error( array( 'message' => 'Your session expired. Please refresh and try again.' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		// Parse the form data using wp-json encoded format.
		$data      = \WPShadow\WPSHADOW_get_post_text( 'data' );
		$form_data = json_decode( $data, true );

		if ( empty( $form_data ) || ! is_array( $form_data ) ) {
			wp_send_json_error( array( 'message' => 'Please check your form and try again.' ) );
		}

		$group = isset( $form_data['group'] ) ? sanitize_key( $form_data['group'] ) : '';

		// Process based on settings group.
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

		wp_send_json_success( array( 'message' => __( 'Settings saved successfully', 'plugin-wpshadow' ) ) );
	}

	/**
	 * Save module registry settings.
	 *
	 * @param array $form_data Form data.
	 * @return void
	 */
	private static function save_module_registry_settings( array $form_data ): void {
		update_option( 'wpshadow_module_discovery_enabled', isset( $form_data['wpshadow_module_discovery_enabled'] ) ? 1 : 0 );
		update_option( 'wpshadow_module_discovery_frequency', sanitize_key( $form_data['wpshadow_module_discovery_frequency'] ?? 'on-demand' ) );
	}

	/**
	 * Save capabilities settings.
	 *
	 * @param array $form_data Form data.
	 * @return void
	 */
	private static function save_capabilities_settings( array $form_data ): void {
		update_option( 'wpshadow_capability_dashboard_role', sanitize_key( $form_data['wpshadow_capability_dashboard_role'] ?? 'manage_options' ) );
		$install_roles = isset( $form_data['wpshadow_capability_install_roles'] ) ? array_map( 'sanitize_key', (array) $form_data['wpshadow_capability_install_roles'] ) : array();
		update_option( 'wpshadow_capability_install_roles', $install_roles );
		$update_roles = isset( $form_data['wpshadow_capability_update_roles'] ) ? array_map( 'sanitize_key', (array) $form_data['wpshadow_capability_update_roles'] ) : array();
		update_option( 'wpshadow_capability_update_roles', $update_roles );
	}

	/**
	 * Save dashboard settings.
	 *
	 * @param array $form_data Form data.
	 * @return void
	 */
	private static function save_dashboard_settings( array $form_data ): void {
		update_option( 'wpshadow_dashboard_default_columns', absint( $form_data['wpshadow_dashboard_default_columns'] ?? 2 ) );
		$sticky_widgets = isset( $form_data['wpshadow_dashboard_sticky_widgets'] ) ? array_map( 'sanitize_key', (array) $form_data['wpshadow_dashboard_sticky_widgets'] ) : array();
		update_option( 'wpshadow_dashboard_sticky_widgets', $sticky_widgets );
		update_option( 'wpshadow_dashboard_widget_sorting', sanitize_key( $form_data['wpshadow_dashboard_widget_sorting'] ?? 'drag-order' ) );
	}

	/**
	 * Save license settings.
	 *
	 * @param array $form_data Form data.
	 * @return void
	 */
	private static function save_license_settings( array $form_data ): void {
		$license_key = isset( $form_data['wpshadow_license_key'] ) ? sanitize_text_field( $form_data['wpshadow_license_key'] ) : '';
		update_option( 'wpshadow_license_key', $license_key );
		$auto_update = isset( $form_data['wpshadow_license_auto_update_types'] ) ? array_map( 'sanitize_key', (array) $form_data['wpshadow_license_auto_update_types'] ) : array();
		update_option( 'wpshadow_license_auto_update_types', $auto_update );
		update_option( 'wpshadow_license_update_channel', sanitize_key( $form_data['wpshadow_license_update_channel'] ?? 'stable' ) );
	}

	/**
	 * Save privacy settings.
	 *
	 * @param array $form_data Form data.
	 * @return void
	 */
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

	/**
	 * Save database cleanup settings.
	 *
	 * @param array $form_data Form data.
	 * @return void
	 */
	private static function save_database_cleanup_settings( array $form_data ): void {
		// Get the database cleanup feature instance.
		$feature = \WPShadow\WPSHADOW_Feature_Registry::get_feature( 'database-cleanup' );
		if ( ! $feature ) {
			wp_send_json_error( array( 'message' => 'Database cleanup feature not found' ) );
		}

		// Update enabled status.
		$enabled = isset( $form_data['wpshadow_database_cleanup_enabled'] ) ? 1 : 0;
		$feature->update_setting( 'enabled', $enabled );

		// Update cleanup frequency.
		$old_frequency = $feature->get_setting( 'cleanup_frequency', 'weekly' );
		$new_frequency = sanitize_key( $form_data['wpshadow_cleanup_frequency'] ?? 'weekly' );
		$feature->update_setting( 'cleanup_frequency', $new_frequency );

		// Update cleanup options.
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

		// Reschedule if frequency changed.
		if ( $old_frequency !== $new_frequency ) {
			// Clear existing schedule.
			$timestamp = wp_next_scheduled( 'wpshadow_database_cleanup' );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'wpshadow_database_cleanup' );
			}

			// Schedule new event if enabled.
			if ( $enabled ) {
				wp_schedule_event( time(), $new_frequency, 'wpshadow_database_cleanup' );
			}
		}
	}
}
