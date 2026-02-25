<?php
/**
 * Import/Export AJAX Handler
 *
 * Handles all AJAX requests for importing, exporting, and syncing settings.
 *
 * Philosophy Alignment:
 * - Commandment #8: Inspire Confidence - Safe operations with backups
 * - Commandment #10: Beyond Pure - Privacy-first data handling
 *
 * @package    WPShadow
 * @subpackage Admin\Ajax
 * @since      1.7035.1500
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Settings_Registry;
use WPShadow\Core\WPShadow_Account_API;
use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import_Export_Handler Class
 *
 * Manages settings import, export, and cloud sync operations
 *
 * @since 1.7035.1500
 */
class Import_Export_Handler extends AJAX_Handler_Base {

	/**
	 * Settings to exclude from export (security-sensitive data)
	 *
	 * @var array
	 */
	private static $excluded_settings = array(
		'wpshadow_account_api_key',
		'wpshadow_email_verification_tokens',
	);

	/**
	 * Register AJAX handlers
	 *
	 * @since  1.7035.1500
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_ajax_wpshadow_export_settings', array( __CLASS__, 'handle_export' ) );
		add_action( 'wp_ajax_wpshadow_import_settings', array( __CLASS__, 'handle_import' ) );
		add_action( 'wp_ajax_wpshadow_sync_to_cloud', array( __CLASS__, 'handle_sync_to_cloud' ) );
		add_action( 'wp_ajax_wpshadow_restore_from_cloud', array( __CLASS__, 'handle_restore_from_cloud' ) );
		add_action( 'wp_ajax_wpshadow_toggle_cloud_sync', array( __CLASS__, 'handle_toggle_cloud_sync' ) );
	}

	/**
	 * Handle settings export
	 *
	 * @since  1.7035.1500
	 * @return void Dies after sending JSON response
	 */
	public static function handle_export() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_export_settings', 'manage_options' );

		try {
			$settings = self::get_all_settings();

			// Log activity
			Activity_Logger::log(
				'settings_exported',
				array(
					'settings_count' => count( $settings ),
					'user_id'        => get_current_user_id(),
				)
			);

			// Update last export timestamp
			update_option( 'wpshadow_last_export_date', current_time( 'mysql' ), false );

			self::send_success(
				array(
					'settings' => $settings,
					'metadata' => array(
						'exported_at'    => current_time( 'mysql' ),
						'site_url'       => get_site_url(),
						'wp_version'     => get_bloginfo( 'version' ),
						'plugin_version' => WPSHADOW_VERSION,
					),
				)
			);
		} catch ( \Exception $e ) {
			self::send_error(
				__( 'Failed to export settings', 'wpshadow' ),
				array( 'error' => $e->getMessage() )
			);
		}
	}

	/**
	 * Handle settings import
	 *
	 * @since  1.7035.1500
	 * @return void Dies after sending JSON response
	 */
	public static function handle_import() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_import_settings', 'manage_options' );

		try {
			// Get settings data from POST
			$settings_json = self::get_post_param( 'settings', 'text', '', true );

			// Decode JSON
			$settings_data = json_decode( $settings_json, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				throw new \Exception( __( 'Invalid JSON format', 'wpshadow' ) );
			}

			if ( ! isset( $settings_data['settings'] ) || ! is_array( $settings_data['settings'] ) ) {
				throw new \Exception( __( 'Invalid settings file format', 'wpshadow' ) );
			}

			// Create backup of current settings
			$backup = self::create_settings_backup();

			// Import settings
			$imported_count = self::import_settings( $settings_data['settings'] );

			// Log activity
			Activity_Logger::log(
				'settings_imported',
				array(
					'settings_count' => $imported_count,
					'backup_id'      => $backup['backup_id'],
					'user_id'        => get_current_user_id(),
				)
			);

			// Update last import timestamp
			update_option( 'wpshadow_last_import_date', current_time( 'mysql' ), false );

			// Clear cache to force re-evaluation
			self::clear_all_caches();

			self::send_success(
				array(
					'imported_count' => $imported_count,
					'backup_id'      => $backup['backup_id'],
					'message'        => sprintf(
						/* translators: %d: number of settings imported */
						__( 'Successfully imported %d settings', 'wpshadow' ),
						$imported_count
					),
				)
			);
		} catch ( \Exception $e ) {
			self::send_error(
				__( 'Failed to import settings', 'wpshadow' ),
				array( 'error' => $e->getMessage() )
			);
		}
	}

	/**
	 * Handle sync to cloud
	 *
	 * @since  1.7035.1500
	 * @return void Dies after sending JSON response
	 */
	public static function handle_sync_to_cloud() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_sync_to_cloud', 'manage_options' );

		// Check if user is registered
		if ( ! WPShadow_Account_API::is_registered() ) {
			self::send_error( __( 'Cloud sync requires a free WPShadow account. Please register to get started.', 'wpshadow' ) );
		}

		try {
			$settings = self::get_all_settings();

			// Prepare data for cloud
			$cloud_data = array(
				'settings' => $settings,
				'metadata' => array(
					'synced_at'      => current_time( 'mysql' ),
					'site_url'       => get_site_url(),
					'wp_version'     => get_bloginfo( 'version' ),
					'plugin_version' => WPSHADOW_VERSION,
				),
			);

			// Call cloud API
			$response = WPShadow_Account_API::make_api_request(
				'/v1/settings/sync',
				'POST',
				$cloud_data
			);

			if ( is_wp_error( $response ) ) {
				throw new \Exception( $response->get_error_message() );
			}

			// Log activity
			Activity_Logger::log(
				'settings_synced_to_cloud',
				array(
					'settings_count' => count( $settings ),
					'user_id'        => get_current_user_id(),
				)
			);

			// Update last sync timestamp
			update_option( 'wpshadow_last_cloud_sync', current_time( 'mysql' ), false );

			self::send_success(
				array(
					'message'   => __( 'Settings synced to cloud successfully', 'wpshadow' ),
					'synced_at' => current_time( 'mysql' ),
				)
			);
		} catch ( \Exception $e ) {
			self::send_error(
				__( 'Failed to sync settings to cloud', 'wpshadow' ),
				array( 'error' => $e->getMessage() )
			);
		}
	}

	/**
	 * Handle restore from cloud
	 *
	 * @since  1.7035.1500
	 * @return void Dies after sending JSON response
	 */
	public static function handle_restore_from_cloud() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_restore_from_cloud', 'manage_options' );

		// Check if user is registered
		if ( ! WPShadow_Account_API::is_registered() ) {
			self::send_error( __( 'Cloud sync requires a free WPShadow account. Please register to get started.', 'wpshadow' ) );
		}

		try {
			// Call cloud API to get settings
			$response = WPShadow_Account_API::make_api_request(
				'/v1/settings/restore',
				'GET'
			);

			if ( is_wp_error( $response ) ) {
				throw new \Exception( $response->get_error_message() );
			}

			if ( ! isset( $response['settings'] ) || ! is_array( $response['settings'] ) ) {
				throw new \Exception( __( 'No cloud backup found', 'wpshadow' ) );
			}

			// Create backup of current settings
			$backup = self::create_settings_backup();

			// Import settings from cloud
			$imported_count = self::import_settings( $response['settings'] );

			// Log activity
			Activity_Logger::log(
				'settings_restored_from_cloud',
				array(
					'settings_count' => $imported_count,
					'backup_id'      => $backup['backup_id'],
					'user_id'        => get_current_user_id(),
				)
			);

			// Clear cache to force re-evaluation
			self::clear_all_caches();

			self::send_success(
				array(
					'imported_count' => $imported_count,
					'backup_id'      => $backup['backup_id'],
					'message'        => sprintf(
						/* translators: %d: number of settings restored */
						__( 'Successfully restored %d settings from cloud', 'wpshadow' ),
						$imported_count
					),
				)
			);
		} catch ( \Exception $e ) {
			self::send_error(
				__( 'Failed to restore settings from cloud', 'wpshadow' ),
				array( 'error' => $e->getMessage() )
			);
		}
	}

	/**
	 * Handle toggle cloud sync
	 *
	 * @since  1.7035.1500
	 * @return void Dies after sending JSON response
	 */
	public static function handle_toggle_cloud_sync() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_toggle_cloud_sync', 'manage_options' );

		// Check if user is registered
		if ( ! WPShadow_Account_API::is_registered() ) {
			self::send_error( __( 'Cloud sync requires a free WPShadow account. Please register to get started.', 'wpshadow' ) );
		}

		try {
			$enabled = self::get_post_param( 'enabled', 'boolean', false );

			update_option( 'wpshadow_cloud_sync_enabled', $enabled, false );

			// Log activity
			Activity_Logger::log(
				$enabled ? 'cloud_sync_enabled' : 'cloud_sync_disabled',
				array( 'user_id' => get_current_user_id() )
			);

			self::send_success(
				array(
					'enabled' => $enabled,
					'message' => $enabled
						? __( 'Cloud sync enabled', 'wpshadow' )
						: __( 'Cloud sync disabled', 'wpshadow' ),
				)
			);
		} catch ( \Exception $e ) {
			self::send_error(
				__( 'Failed to toggle cloud sync', 'wpshadow' ),
				array( 'error' => $e->getMessage() )
			);
		}
	}

	/**
	 * Get all WPShadow settings
	 *
	 * @since  1.7035.1500
	 * @return array Settings array
	 */
	private static function get_all_settings(): array {
		$all_options = wp_load_alloptions();

		$settings = array();

		foreach ( $all_options as $option_name => $option_value ) {
			if ( 0 !== strpos( (string) $option_name, 'wpshadow_' ) ) {
				continue;
			}

			// Skip excluded settings (sensitive data)
			if ( in_array( $option_name, self::$excluded_settings, true ) ) {
				continue;
			}

			// Skip transients and cache
			if ( strpos( $option_name, '_transient_' ) !== false ) {
				continue;
			}

			$settings[ $option_name ] = maybe_unserialize( $option_value );
		}

		return $settings;
	}

	/**
	 * Import settings
	 *
	 * @since  1.7035.1500
	 * @param  array $settings Settings to import.
	 * @return int Number of settings imported
	 */
	private static function import_settings( array $settings ): int {
		$imported = 0;

		foreach ( $settings as $option_name => $option_value ) {
			// Verify this is a wpshadow_ setting
			if ( strpos( $option_name, 'wpshadow_' ) !== 0 ) {
				continue;
			}

			// Skip excluded settings
			if ( in_array( $option_name, self::$excluded_settings, true ) ) {
				continue;
			}

			// Update option
			update_option( $option_name, $option_value, false );
			++$imported;
		}

		return $imported;
	}

	/**
	 * Create settings backup
	 *
	 * @since  1.7035.1500
	 * @return array Backup info
	 */
	private static function create_settings_backup(): array {
		$settings  = self::get_all_settings();
		$backup_id = 'backup_' . time();

		$backup_data = array(
			'backup_id'  => $backup_id,
			'created_at' => current_time( 'mysql' ),
			'settings'   => $settings,
		);

		// Store backup (keep last 5 backups)
		$backups               = get_option( 'wpshadow_settings_backups', array() );
		$backups[ $backup_id ] = $backup_data;

		// Keep only last 5 backups
		if ( count( $backups ) > 5 ) {
			$backups = array_slice( $backups, -5, 5, true );
		}

		update_option( 'wpshadow_settings_backups', $backups, false );

		return $backup_data;
	}

	/**
	 * Clear all caches
	 *
	 * @since  1.7035.1500
	 * @return void
	 */
	private static function clear_all_caches(): void {
		// Clear WordPress object cache
		wp_cache_flush();

		// Clear WPShadow diagnostics cache
		delete_transient( 'wpshadow_diagnostics_cache' );
		delete_transient( 'wpshadow_findings_cache' );

		// Trigger cache clear action
		do_action( 'wpshadow_settings_imported' );
	}
}

// Initialize AJAX handlers
Import_Export_Handler::init();
