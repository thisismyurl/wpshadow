<?php

declare(strict_types=1);

namespace WPShadow\Core;

/**
 * Settings Registry - Centralized WordPress Settings API Registration
 *
 * Registers all WPShadow settings using proper WordPress Settings API.
 * Provides sanitization, validation, defaults, and proper WordPress integration.
 *
 * Philosophy Alignment:
 * - Commandment #8: Inspire Confidence - Uses native WordPress patterns
 * - Commandment #10: Beyond Pure Privacy - Opt-in defaults for privacy settings
 *
 * @package WPShadow
 * @subpackage Core
 */
class Settings_Registry {


	/**
	 * Register hooks
	 */
	public static function register(): void
	{
		add_action('admin_init', [__CLASS__, 'register_all_settings']);
		add_action('update_option', [__CLASS__, 'on_setting_updated'], 10, 3);
		add_action('add_option', [__CLASS__, 'on_setting_added'], 10, 2);
	}

	/**
	 * Hook called when a setting is updated.
	 *
	 * @param string $option    Name of the updated option.
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 */
	public static function on_setting_updated( $option, $old_value, $value ): void {
		// Only fire for WPShadow settings (check for prefix).
		// Use str_starts_with for PHP 8.0+, fallback to strpos for earlier versions.
		if ( function_exists( 'str_starts_with' ) ) {
			if ( ! str_starts_with( $option, 'wpshadow_' ) ) {
				return;
			}
		} else {
			if ( 0 !== strpos( $option, 'wpshadow_' ) ) {
				return;
			}
		}

		/**
		 * Fires when a WPShadow setting is updated.
		 *
		 * @param string $option    Setting name.
		 * @param mixed  $old_value Previous value.
		 * @param mixed  $value     New value.
		 */
		do_action( 'wpshadow_setting_updated', $option, $old_value, $value );

		/**
		 * Fires when a specific WPShadow setting is updated.
		 *
		 * The dynamic portion of the hook name, $option, refers to the setting name.
		 * For example: wpshadow_setting_updated_wpshadow_debug_mode
		 *
		 * @param mixed $old_value Previous value.
		 * @param mixed $value     New value.
		 */
		do_action( "wpshadow_setting_updated_{$option}", $old_value, $value );
	}

	/**
	 * Hook called when a setting is added.
	 *
	 * @param string $option Name of the added option.
	 * @param mixed  $value  Value of the added option.
	 */
	public static function on_setting_added( $option, $value ): void {
		// Only fire for WPShadow settings (check for prefix).
		// Use str_starts_with for PHP 8.0+, fallback to strpos for earlier versions.
		if ( function_exists( 'str_starts_with' ) ) {
			if ( ! str_starts_with( $option, 'wpshadow_' ) ) {
				return;
			}
		} else {
			if ( 0 !== strpos( $option, 'wpshadow_' ) ) {
				return;
			}
		}

		/**
		 * Fires when a WPShadow setting is added.
		 *
		 * @param string $option Setting name.
		 * @param mixed  $value  Setting value.
		 */
		do_action( 'wpshadow_setting_added', $option, $value );

		/**
		 * Fires when a specific WPShadow setting is added.
		 *
		 * The dynamic portion of the hook name, $option, refers to the setting name.
		 *
		 * @param mixed $value Setting value.
		 */
		do_action( "wpshadow_setting_added_{$option}", $value );
	}

	/**
	 * Register all WPShadow settings with WordPress Settings API
	 *
	 * Each setting gets:
	 * - Type validation
	 * - Sanitization callback
	 * - Default value
	 * - REST API exposure control (privacy-aware)
	 */
	public static function register_all_settings(): void {

		// =================================================================
		// GUARDIAN SETTINGS GROUP
		// =================================================================

		register_setting(
			'wpshadow_guardian_settings',
			'wpshadow_guardian_enabled',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false, // Privacy - don't expose to REST
				'description'       => __( 'Enable WPShadow Guardian automated monitoring', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_guardian_settings',
			'wpshadow_guardian_safety_mode',
			array(
				'type'              => 'boolean',
				'default'           => true, // Default to safe (philosophy: helpful, not pushy)
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Require manual confirmation for auto-fixes', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_guardian_settings',
			'wpshadow_guardian_activity_logging',
			array(
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Log all Guardian actions for audit trail', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_guardian_settings',
			'wpshadow_guardian_check_frequency',
			array(
				'type'              => 'string',
				'default'           => 'hourly',
				'sanitize_callback' => array( __CLASS__, 'sanitize_frequency' ),
				'show_in_rest'      => false,
				'description'       => __( 'How often Guardian runs automated checks', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_guardian_settings',
			'wpshadow_guardian_max_treatments',
			array(
				'type'              => 'integer',
				'default'           => 5,
				'sanitize_callback' => array( __CLASS__, 'sanitize_max_treatments' ),
				'show_in_rest'      => false,
				'description'       => __( 'Maximum treatments per Guardian run', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_guardian_settings',
			'wpshadow_guardian_auto_fix_whitelist',
			array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( __CLASS__, 'sanitize_treatment_whitelist' ),
				'show_in_rest'      => false,
				'description'       => __( 'Treatments approved for automatic execution', 'wpshadow' ),
			)
		);

		// =================================================================
		// WPSHADOW ACCOUNT SETTINGS (Unified Registration)
		// =================================================================

		register_setting(
			'wpshadow_account_settings',
			'wpshadow_account_api_key',
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => false, // Security - never expose API key
				'description'       => __( 'WPShadow account API key', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_account_settings',
			'wpshadow_account_email',
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_email',
				'show_in_rest'      => false, // Privacy - don't expose email
				'description'       => __( 'WPShadow account email address', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_account_settings',
			'wpshadow_account_registered_at',
			array(
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'show_in_rest'      => false,
				'description'       => __( 'Registration timestamp', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_account_settings',
			'wpshadow_account_services',
			array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( __CLASS__, 'sanitize_services_array' ),
				'show_in_rest'      => false,
				'description'       => __( 'Enabled services and free tier limits', 'wpshadow' ),
			)
		);

		// =================================================================
		// WORKFLOW SETTINGS GROUP
		// =================================================================

		register_setting(
			'wpshadow_workflow_settings',
			'wpshadow_approved_email_recipients',
			array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( __CLASS__, 'sanitize_email_recipients' ),
				'show_in_rest'      => false, // Privacy - email addresses
				'description'       => __( 'Pre-approved email recipients for workflow notifications', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_workflow_settings',
			'wpshadow_email_verification_tokens',
			array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( __CLASS__, 'sanitize_verification_tokens' ),
				'show_in_rest'      => false, // Security - tokens
				'description'       => __( 'Pending email verification tokens', 'wpshadow' ),
			)
		);

		// =================================================================
		// PRIVACY SETTINGS GROUP (Philosophy: Beyond Pure Privacy #10)
		// =================================================================

		register_setting(
			'wpshadow_privacy_settings',
			'wpshadow_telemetry_enabled',
			array(
				'type'              => 'boolean',
				'default'           => false, // Privacy-first: opt-in, not opt-out
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Share anonymous usage data to improve WPShadow', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_privacy_settings',
			'wpshadow_telemetry_consent_date',
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => false,
				'description'       => __( 'Date user consented to telemetry', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_privacy_settings',
			'wpshadow_error_reporting',
			array(
				'type'              => 'boolean',
				'default'           => false, // Opt-in
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Share error reports with WPShadow team', 'wpshadow' ),
			)
		);

		// =================================================================
		// GENERAL SETTINGS GROUP
		// =================================================================

		register_setting(
			'wpshadow_settings',
			'wpshadow_cache_enabled',
			array(
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Enable diagnostic results caching', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_cache_duration',
			array(
				'type'              => 'integer',
				'default'           => 3600, // 1 hour
				'sanitize_callback' => 'absint',
				'show_in_rest'      => false,
				'description'       => __( 'Cache duration in seconds', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_debug_mode',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Enable debug logging', 'wpshadow' ),
			)
		);

		// =================================================================
		// BACKUP SETTINGS (Vault Light)
		// =================================================================

		register_setting(
			'wpshadow_settings',
			'wpshadow_backup_enabled',
			array(
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Enable backup snapshots before treatments', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_backup_include_database',
			array(
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Include database in Vault Light snapshots', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_backup_retention_days',
			array(
				'type'              => 'integer',
				'default'           => 7,
				'sanitize_callback' => array( __CLASS__, 'sanitize_retention_days' ),
				'show_in_rest'      => false,
				'description'       => __( 'Number of days to keep Vault Light backups', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_backup_max_size_mb',
			array(
				'type'              => 'integer',
				'default'           => 500,
				'sanitize_callback' => 'absint',
				'show_in_rest'      => false,
				'description'       => __( 'Maximum total backup size (MB)', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_backup_compress',
			array(
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Compress Vault Light backups', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_backup_exclude_uploads',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Exclude uploads from Vault Light backups', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_backup_verify',
			array(
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Verify Vault Light backups after creation', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_magic_link_expiry_notifications',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Send email notifications when magic links expire', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_backup_schedule_enabled',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Enable scheduled Vault Light backups', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_backup_schedule_frequency',
			array(
				'type'              => 'string',
				'default'           => 'weekly',
				'sanitize_callback' => array( __CLASS__, 'sanitize_backup_frequency' ),
				'show_in_rest'      => false,
				'description'       => __( 'How often scheduled backups run', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_backup_schedule_time',
			array(
				'type'              => 'string',
				'default'           => '02:00',
				'sanitize_callback' => array( __CLASS__, 'sanitize_backup_time' ),
				'show_in_rest'      => false,
				'description'       => __( 'Time of day for scheduled backups (24h)', 'wpshadow' ),
			)
		);

		// =================================================================
		// DIAGNOSTIC & TREATMENT TOGGLES
		// =================================================================

		// Disabled diagnostic classes (fully-qualified class names)
		register_setting(
			'wpshadow_settings',
			'wpshadow_disabled_diagnostic_classes',
			array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( __CLASS__, 'sanitize_class_list' ),
				'show_in_rest'      => false,
				'description'       => __( 'List of diagnostic classes disabled by admin', 'wpshadow' ),
			)
		);

		// Disabled treatment classes (fully-qualified class names)
		register_setting(
			'wpshadow_settings',
			'wpshadow_disabled_treatment_classes',
			array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( __CLASS__, 'sanitize_class_list' ),
				'show_in_rest'      => false,
				'description'       => __( 'List of treatment classes disabled by admin', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_kb_link_enabled',
			array(
				'type'              => 'boolean',
				'default'           => true, // Philosophy: Drive to KB (#5)
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Show knowledge base links in diagnostics', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_training_link_enabled',
			array(
				'type'              => 'boolean',
				'default'           => true, // Philosophy: Drive to Training (#6)
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Show training video links in treatments', 'wpshadow' ),
			)
		);

		// =================================================================
		// PERFORMANCE SETTINGS
		// =================================================================

		register_setting(
			'wpshadow_settings',
			'wpshadow_heartbeat_settings',
			array(
				'type'              => 'array',
				'default'           => array(
					'dashboard' => 60,
					'editor'    => 15,
					'frontend'  => 'default',
				),
				'sanitize_callback' => array( __CLASS__, 'sanitize_heartbeat_settings' ),
				'show_in_rest'      => false,
				'description'       => __( 'WordPress Heartbeat API optimization settings', 'wpshadow' ),
			)
		);

		// =================================================================
		// VISUAL COMPARISON SETTINGS
		// =================================================================

		register_setting(
			'wpshadow_settings',
			'wpshadow_visual_comparison_enabled',
			array(
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Enable visual comparison screenshots before/after treatments', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_visual_comparison_retention_days',
			array(
				'type'              => 'integer',
				'default'           => 30,
				'sanitize_callback' => array( __CLASS__, 'sanitize_retention_days' ),
				'show_in_rest'      => false,
				'description'       => __( 'Number of days to keep visual comparison screenshots', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_visual_comparison_width',
			array(
				'type'              => 'integer',
				'default'           => 1200,
				'sanitize_callback' => array( __CLASS__, 'sanitize_screenshot_dimension' ),
				'show_in_rest'      => false,
				'description'       => __( 'Screenshot width in pixels', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_settings',
			'wpshadow_visual_comparison_height',
			array(
				'type'              => 'integer',
				'default'           => 800,
				'sanitize_callback' => array( __CLASS__, 'sanitize_screenshot_dimension' ),
				'show_in_rest'      => false,
				'description'       => __( 'Screenshot height in pixels', 'wpshadow' ),
			)
		);

		// =================================================================
		// EXIT FOLLOWUP SETTINGS (Philosophy: Commandment #1, #4, #10)
		// =================================================================

		register_setting(
			'wpshadow_exit_followup_settings',
			'wpshadow_exit_followup_enabled',
			array(
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Enable exit interview followup scheduling', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_exit_followup_settings',
			'wpshadow_exit_followup_immediate_days',
			array(
				'type'              => 'integer',
				'default'           => 3,
				'sanitize_callback' => array( __CLASS__, 'sanitize_followup_days' ),
				'show_in_rest'      => false,
				'description'       => __( 'Days to wait for immediate followup (competitor intel)', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_exit_followup_settings',
			'wpshadow_exit_followup_short_term_days',
			array(
				'type'              => 'integer',
				'default'           => 14,
				'sanitize_callback' => array( __CLASS__, 'sanitize_followup_days' ),
				'show_in_rest'      => false,
				'description'       => __( 'Days to wait for short-term followup (feature needs)', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_exit_followup_settings',
			'wpshadow_exit_followup_long_term_days',
			array(
				'type'              => 'integer',
				'default'           => 30,
				'sanitize_callback' => array( __CLASS__, 'sanitize_followup_days' ),
				'show_in_rest'      => false,
				'description'       => __( 'Days to wait for long-term followup (general feedback)', 'wpshadow' ),
			)
		);

		register_setting(
			'wpshadow_exit_followup_settings',
			'wpshadow_exit_followup_auto_send',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'description'       => __( 'Automatically send followup emails (requires email service)', 'wpshadow' ),
			)
		);
	}

	/**
	 * Sanitize services array
	 *
	 * @param mixed $value Input value
	 * @return array Sanitized services configuration
	 */
	public static function sanitize_services_array( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$valid_services = array( 'guardian', 'vault', 'cloud' );
		$sanitized      = array();

		foreach ( $value as $service => $config ) {
			if ( ! in_array( $service, $valid_services, true ) || ! is_array( $config ) ) {
				continue;
			}

			$sanitized[ $service ] = array(
				'tier' => isset( $config['tier'] ) ? sanitize_text_field( $config['tier'] ) : 'free',
			);

			// Add service-specific fields.
			foreach ( $config as $key => $val ) {
				if ( 'tier' === $key ) {
					continue;
				}
				// Sanitize based on type.
				if ( is_int( $val ) ) {
					$sanitized[ $service ][ $key ] = absint( $val );
				} elseif ( is_bool( $val ) ) {
					$sanitized[ $service ][ $key ] = rest_sanitize_boolean( $val );
				} elseif ( is_string( $val ) ) {
					$sanitized[ $service ][ $key ] = sanitize_text_field( $val );
				}
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize frequency value
	 *
	 * @param mixed $value Input value
	 * @return string Sanitized frequency
	 */
	public static function sanitize_frequency( $value ): string {
		$valid = array( 'hourly', 'twicedaily', 'daily', 'weekly' );
		return in_array( $value, $valid, true ) ? $value : 'hourly';
	}

	/**
	 * Sanitize max treatments value
	 *
	 * @param mixed $value Input value
	 * @return int Sanitized value (1-20)
	 */
	public static function sanitize_max_treatments( $value ): int {
		$int = absint( $value );
		return min( max( $int, 1 ), 20 ); // Clamp between 1-20
	}

	/**
	 * Sanitize treatment whitelist
	 *
	 * @param mixed $value Input value
	 * @return array Sanitized treatment IDs
	 */
	public static function sanitize_treatment_whitelist( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		// Only allow alphanumeric, underscore, dash
		return array_values( array_filter( array_map( 'sanitize_key', $value ) ) );
	}

	/**
	 * Sanitize email recipients array
	 *
	 * @param mixed $value Input value
	 * @return array Sanitized recipients
	 */
	public static function sanitize_email_recipients( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $value as $email => $data ) {
			if ( is_email( $email ) ) {
				$sanitized[ sanitize_email( $email ) ] = array(
					'approved'      => ! empty( $data['approved'] ),
					'pending_admin' => ! empty( $data['pending_admin'] ),
					'added_date'    => sanitize_text_field( $data['added_date'] ?? '' ),
					'added_by'      => absint( $data['added_by'] ?? 0 ),
				);
			}
		}
		return $sanitized;
	}

	/**
	 * Sanitize verification tokens
	 *
	 * @param mixed $value Input value
	 * @return array Sanitized tokens
	 */
	public static function sanitize_verification_tokens( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$sanitized = array();
		$now       = current_time( 'timestamp' );

		foreach ( $value as $token => $data ) {
			// Remove expired tokens during sanitization
			if ( isset( $data['expires'] ) && $data['expires'] < $now ) {
				continue;
			}

			$sanitized[ sanitize_key( $token ) ] = array(
				'email'   => sanitize_email( $data['email'] ?? '' ),
				'created' => absint( $data['created'] ?? 0 ),
				'expires' => absint( $data['expires'] ?? 0 ),
			);
		}
		return $sanitized;
	}

	/**
	 * Sanitize heartbeat settings
	 *
	 * @param mixed $value Input value
	 * @return array Sanitized settings
	 */
	public static function sanitize_heartbeat_settings( $value ): array {
		if ( ! is_array( $value ) ) {
			return array(
				'dashboard' => 60,
				'editor'    => 15,
				'frontend'  => 'default',
			);
		}

		$defaults = array(
			'dashboard' => 60,
			'editor'    => 15,
			'frontend'  => 'default',
		);

		$sanitized = array();
		foreach ( $defaults as $key => $default ) {
			if ( 'frontend' === $key ) {
				// Frontend can be 'default', 'disabled', or integer
				$sanitized[ $key ] = 'disabled' === $value[ $key ] ? 'disabled' : ( is_numeric( $value[ $key ] ) ? absint( $value[ $key ] ) : 'default' );
			} else {
				// Dashboard and editor must be integers (15-120)
				$int               = absint( $value[ $key ] ?? $default );
				$sanitized[ $key ] = min( max( $int, 15 ), 120 );
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize retention days for visual comparisons
	 *
	 * @param mixed $value Input value
	 * @return int Sanitized value (7-365 days)
	 */
	public static function sanitize_retention_days( $value ): int {
		$int = absint( $value );
		return min( max( $int, 7 ), 365 ); // Clamp between 7-365 days
	}

	/**
	 * Sanitize screenshot dimension
	 *
	 * @param mixed $value Input value
	 * @return int Sanitized value (400-2560 pixels)
	 */
	public static function sanitize_screenshot_dimension( $value ): int {
		$int = absint( $value );
		return min( max( $int, 400 ), 2560 ); // Clamp between 400-2560 pixels
	}

	/**
	 * Sanitize backup frequency
	 *
	 * @param mixed $value Input value
	 * @return string Sanitized frequency
	 */
	public static function sanitize_backup_frequency( $value ): string {
		$allowed = array( 'daily', 'weekly', 'monthly' );
		$value   = sanitize_key( (string) $value );
		return in_array( $value, $allowed, true ) ? $value : 'weekly';
	}

	/**
	 * Sanitize backup time (HH:MM 24h)
	 *
	 * @param mixed $value Input value
	 * @return string Sanitized time
	 */
	public static function sanitize_backup_time( $value ): string {
		$value = is_string( $value ) ? trim( $value ) : '';
		if ( preg_match( '/^([01]\d|2[0-3]):([0-5]\d)$/', $value ) ) {
			return $value;
		}

		return '02:00';
	}

	/**
	 * Sanitize followup days value
	 *
	 * @param mixed $value Input value
	 * @return int Sanitized value (1-90 days)
	 */
	public static function sanitize_followup_days( $value ): int {
		$int = absint( $value );
		return min( max( $int, 1 ), 90 ); // Clamp between 1-90 days
	}

	/**
	 * Sanitize a list of class identifiers (fully-qualified class names)
	 *
	 * Allows only letters, numbers, underscores, and namespace separators (\\).
	 * Discards any invalid entries.
	 *
	 * @param mixed $value Input value
	 * @return array Sanitized class names
	 */
	public static function sanitize_class_list( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $value as $class ) {
			$raw = is_string( $class ) ? $class : '';
			// Allow namespace separators and typical class characters
			if ( preg_match( '/^[A-Za-z0-9_\\\\\\]+$/', $raw ) ) {
				$sanitized[] = $raw;
			}
		}

		// De-duplicate
		return array_values( array_unique( $sanitized ) );
	}

	// =================================================================
	// POST TYPES SETTINGS
	// =================================================================

	/**
	 * Get active post types.
	 *
	 * @since  1.26033.1530
	 * @return array Active post type keys.
	 */
	public static function get_active_post_types(): array {
		return get_option( 'wpshadow_active_post_types', array() );
	}

	/**
	 * Set active post types.
	 *
	 * @since  1.26033.1530
	 * @param  array $post_types Active post type keys.
	 * @return bool Whether the update succeeded.
	 */
	public static function set_active_post_types( array $post_types ): bool {
		return update_option( 'wpshadow_active_post_types', $post_types, false );
	}
}

