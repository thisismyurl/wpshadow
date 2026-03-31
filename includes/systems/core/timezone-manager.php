<?php
/**
 * Timezone Manager - Detects and manages admin timezone
 *
 * OVERVIEW:
 * Automatically detects the current user's browser timezone using Intl.DateTimeFormat API
 * and synchronizes WordPress timezone settings to match the admin's actual location.
 * This overrides the server timezone which may be completely different.
 *
 * USAGE:
 * 1. Timezone_Manager::init() hooks into admin (called from wpshadow.php)
 * 2. JavaScript detects browser timezone on each admin page load
 * 3. AJAX sends detection to server, stored in wp_options
 * 4. WordPress timezone_string option updated automatically
 * 5. Manual tool at /wp-admin/?page=wpshadow-utilities&tool=timezone-alignment allows override
 *
 * SECURITY:
 * - Uses sanitize_text_field on all timezone inputs
 * - Validates against DateTimeZone before applying
 * - Uses nonce verification on all AJAX endpoints
 * - Checks manage_options capability
 *
 * FLOW:
 * Admin loads page → JS detects Intl.DateTimeFormat().timeZone
 *                → AJAX sends to wpshadow_detect_timezone
 *                → Server validates & stores in wp_options
 *                → Updates WordPress timezone_string option
 *                → Optional: Manual override via settings tool
 *
 * TIMEZONE MAPPING:
 * Browser Intl API returns IANA names like: America/Denver, America/New_York
 * WordPress uses same format in timezone_string option
 * Abbreviations calculated on-the-fly (MST, EST, etc.)
 *
 * @package WPShadow
 * @subpackage Core
 */

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages admin timezone detection and synchronization
 */
class Timezone_Manager {

	const OPTION_KEY    = 'wpshadow_admin_timezone';
	const USER_META_KEY = 'wpshadow_timezone';

	/**
	 * Initialize timezone manager
	 * Hooks into admin to detect and set timezone
	 *
	 * NOTE: AJAX handlers now registered via AJAX_Router in class-ajax-router.php
	 * See: includes/admin/ajax/class-detect-timezone-handler.php
	 * See: includes/admin/ajax/class-set-timezone-handler.php
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_timezone_detection' ) );
	}

	/**
	 * Enqueue timezone detection script
	 * Runs on admin pages to detect user's browser timezone
	 */
	public static function enqueue_timezone_detection() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || false === strpos( (string) $screen->id, 'wpshadow' ) ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-timezone-detection',
			plugin_dir_url( __FILE__ ) . '../../assets/js/timezone-detection.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-timezone-detection',
			'wpshadowTimezone',
			'wpshadow_timezone_nonce',
			array(
				'current' => self::get_admin_timezone(),
			)
		);
	}

	/**
	 * AJAX handlers have been migrated to class-based handlers.
	 * See: includes/admin/ajax/class-detect-timezone-handler.php
	 * See: includes/admin/ajax/class-set-timezone-handler.php
	 *
	 * Registered via AJAX_Router in class-ajax-router.php
	 */

	/**
	 * Get current admin timezone
	 * Returns stored admin timezone or WordPress default
	 *
	 * @return string Timezone string (e.g., 'America/Denver', 'America/New_York')
	 */
	public static function get_admin_timezone() {
		// Check for stored WPShadow timezone setting
		$timezone = get_option( self::OPTION_KEY );

		if ( $timezone && self::is_valid_timezone( $timezone ) ) {
			return $timezone;
		}

		// Fall back to WordPress timezone setting
		return get_option( 'timezone_string' ) ?: 'UTC';
	}

	/**
	 * Set admin timezone
	 * Updates both WPShadow option and WordPress timezone_string
	 *
	 * @param string $timezone Valid PHP timezone string
	 * @return bool Success
	 */
	public static function set_admin_timezone( $timezone ) {
		if ( ! self::is_valid_timezone( $timezone ) ) {
			return false;
		}

		// Store in WPShadow option
		update_option( self::OPTION_KEY, $timezone );

		// Update WordPress timezone setting
		update_option( 'timezone_string', $timezone );

		// Also store in current user meta for potential future use
		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), self::USER_META_KEY, $timezone );
		}

		return true;
	}

	/**
	 * Validate timezone string
	 * Checks if timezone is valid PHP identifier
	 *
	 * @param string $timezone Timezone to validate
	 * @return bool True if valid
	 */
	public static function is_valid_timezone( $timezone ) {
		if ( empty( $timezone ) || ! is_string( $timezone ) ) {
			return false;
		}

		// List of common US timezones for quick validation
		$common_zones = array(
			'America/Anchorage',      // AKST/AKDT
			'America/Denver',         // MST/MDT
			'America/Chicago',        // CST/CDT
			'America/New_York',       // EST/EDT
			'America/Phoenix',        // MST (no DST)
			'America/Los_Angeles',    // PST/PDT
			'Pacific/Honolulu',       // HST
			'America/Adak',           // HST
			'America/Boise',          // MST/MDT
			'America/Detroit',        // EST/EDT
			'America/Indiana/Indianapolis', // EST/EDT
			'America/North_Dakota/Center',  // CST/CDT
			'America/Guatemala',      // CST
			'America/Mexico_City',    // CST/CDT
			'America/Toronto',        // EST/EDT
			'America/Vancouver',      // PST/PDT
			'UTC',
		);

		// Check if it's in common list
		if ( in_array( $timezone, $common_zones, true ) ) {
			return true;
		}

		// For other timezones, use DateTimeZone validation
		try {
			new \DateTimeZone( $timezone );
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Get timezone abbreviation for display
	 * Converts America/Denver to MST/MDT
	 *
	 * @param string $timezone Timezone string
	 * @return string Abbreviation like 'MST' or 'EST'
	 */
	public static function get_timezone_abbreviation( $timezone ) {
		try {
			$tz  = new \DateTimeZone( $timezone );
			$now = new \DateTime( 'now', $tz );
			return $now->format( 'T' );
		} catch ( \Exception $e ) {
			return '';
		}
	}

	/**
	 * Get timezone offset from UTC
	 * Useful for displaying timezone info
	 *
	 * @param string $timezone Timezone string
	 * @return string Offset like '-07:00' or '-06:00'
	 */
	public static function get_timezone_offset( $timezone ) {
		try {
			$tz  = new \DateTimeZone( $timezone );
			$now = new \DateTime( 'now', $tz );
			return $now->format( 'P' );
		} catch ( \Exception $e ) {
			return '';
		}
	}

	/**
	 * Get list of US timezones for dropdown
	 * Organized by region with common names
	 *
	 * @return array Timezone => Label
	 */
	public static function get_us_timezones() {
		return array(
			'Pacific'  => array(
				'Pacific/Honolulu'    => 'Hawaii (HST)',
				'America/Anchorage'   => 'Alaska (AKST/AKDT)',
				'America/Los_Angeles' => 'Pacific (PST/PDT)',
				'America/Phoenix'     => 'Arizona (MST)',
				'America/Boise'       => 'Mountain (MST/MDT)',
			),
			'Mountain' => array(
				'America/Denver'              => 'Mountain (MST/MDT)',
				'America/North_Dakota/Center' => 'North Dakota (CST/CDT)',
			),
			'Central'  => array(
				'America/Chicago' => 'Central (CST/CDT)',
			),
			'Eastern'  => array(
				'America/Detroit'              => 'Eastern (EST/EDT)',
				'America/New_York'             => 'Eastern (EST/EDT)',
				'America/Indiana/Indianapolis' => 'Indiana (EST/EDT)',
			),
		);
	}

	/**
	 * Get WordPress full timezone list (same as WordPress uses)
	 * Uses WordPress's wp_timezone_choice() output
	 *
	 * @param string $selected_zone Currently selected timezone
	 * @return string HTML select options
	 */
	public static function get_wordpress_timezone_list( $selected_zone = '' ) {
		// Use WordPress's built-in timezone list function
		// This returns the exact same list WordPress uses in Settings > General
		return wp_timezone_choice( $selected_zone );
	}

	/**
	 * Get timezone recommendation based on current time
	 * Helpful for detecting if timezone is way off
	 *
	 * @return array Suggestion data
	 */
	public static function get_timezone_suggestion() {
		$current_tz = self::get_admin_timezone();

		// Get server timezone
		$server_tz = date_default_timezone_get();

		// Calculate time difference
		try {
			$current_time = new \DateTime( 'now', new \DateTimeZone( $current_tz ) );
			$server_time  = new \DateTime( 'now', new \DateTimeZone( $server_tz ) );

			$diff       = $current_time->diff( $server_time );
			$hours_diff = $diff->h + ( $diff->days * 24 );

			if ( $hours_diff > 3 ) {
				return array(
					'needs_adjustment' => true,
					'current'          => $current_tz,
					'server'           => $server_tz,
					'difference_hours' => $hours_diff,
					'message'          => sprintf(
						__( 'Admin timezone differs from server by %1$d hours. Server runs in %2$s but admin is in %3$s.', 'wpshadow' ),
						$hours_diff,
						$server_tz,
						$current_tz
					),
				);
			}
		} catch ( \Exception $e ) {
			// Silently handle timezone exceptions
		}

		return array( 'needs_adjustment' => false );
	}
}
