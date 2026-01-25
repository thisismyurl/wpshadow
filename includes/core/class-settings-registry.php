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
	public static function register(): void {
		add_action( 'admin_init', array( __CLASS__, 'register_all_settings' ) );
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
}
