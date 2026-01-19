<?php declare(strict_types=1);
/**
 * Dark Mode Feature
 *
 * Provides dark mode support for the WordPress admin interface with system
 * preference detection and user override capabilities.
 *
 * @package    WPShadow
 * @subpackage Features
 * @since      1.0.0
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

/**
 * Dark Mode Feature Class
 *
 * Enables dark mode theming for WordPress admin pages with automatic system
 * preference detection and manual user override options.
 *
 * @since 1.0.0
 */
final class WPSHADOW_Feature_Dark_Mode extends WPSHADOW_Abstract_Feature {

	/**
	 * Feature constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'dark-mode',
				'name'               => __( 'Admin Dark Mode', 'wpshadow' ),
				'description_short'  => __( 'Dark mode theming for WordPress admin interface', 'wpshadow' ),
				'description_long'   => __( 'Provides comprehensive dark mode support for the WordPress admin interface, reducing eye strain during extended use. Automatically detects system-wide dark mode preferences from the user\'s operating system and applies appropriate styling. Includes manual override controls, smooth transitions, and full compatibility with WordPress color schemes. Features include admin bar toggle, per-user preferences, and seamless integration with existing WordPress themes.', 'wpshadow' ),
				'description_wizard' => __( 'Enable dark mode for WordPress admin pages to reduce eye strain and improve readability in low-light conditions. Automatically detects your system preferences or allows manual control.', 'wpshadow' ),
				'aliases'            => array( 'dark theme', 'night mode', 'dark admin', 'admin theme', 'eye strain', 'ui theme' ),
				'sub_features'       => array(
					'respect_system_preference' => array(
						'name'               => __( 'Respect System Preference', 'wpshadow' ),
						'description_short'  => __( 'Auto-detect OS dark mode setting', 'wpshadow' ),
						'description_long'   => __( 'Automatically detects and respects the dark mode setting from the user\'s operating system. Uses the prefers-color-scheme media query to detect system preferences and applies dark mode accordingly. Works with macOS Dark Mode, Windows dark theme, and Linux desktop environment settings. Updates in real-time when the user changes their system theme without requiring page refresh.', 'wpshadow' ),
						'description_wizard' => __( 'Automatically switch to dark mode when your operating system is set to use a dark theme.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'user_override'             => array(
						'name'               => __( 'User Override', 'wpshadow' ),
						'description_short'  => __( 'Allow manual dark mode toggle', 'wpshadow' ),
						'description_long'   => __( 'Allows users to manually override the automatic dark mode detection and choose their preferred theme. Adds a toggle button to the admin bar for quick switching between light, dark, and auto modes. Preferences are saved per-user and persist across sessions. Includes smooth transitions between themes and remembers the user\'s last choice. Perfect for users who want dark mode for WordPress admin but light mode for their OS (or vice versa).', 'wpshadow' ),
						'description_wizard' => __( 'Let users manually toggle dark mode on/off regardless of their system settings. Adds a button to the admin bar for easy switching.', 'wpshadow' ),
						'default_enabled'    => true,
					),
				),
			)
		);
	}

	/**
	 * Check if feature has details page.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register feature hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		// Enqueue dark mode assets
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dark_mode_assets' ) );

		// AJAX handler for setting dark mode preference
		add_action( 'wp_ajax_wpshadow_set_dark_mode', array( $this, 'ajax_set_dark_mode' ) );

		// Add dark mode toggle to admin bar
		if ( $this->is_sub_feature_enabled( 'user_override' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_toggle' ), 100 );
		}
	}

	/**
	 * Enqueue dark mode assets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_dark_mode_assets(): void {
		// Only on WPShadow pages
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'wpshadow' ) ) {
			return;
		}

		// Enqueue dark mode CSS
		wp_enqueue_style(
			'wpshadow-dark-mode',
			WPSHADOW_URL . 'assets/css/dark-mode.css',
			array(),
			WPSHADOW_VERSION
		);

		// Enqueue dark mode JS
		wp_enqueue_script(
			'wpshadow-dark-mode',
			WPSHADOW_URL . 'assets/js/dark-mode.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		// Localize script
		wp_localize_script(
			'wpshadow-dark-mode',
			'wpshadowDarkMode',
			array(
				'nonce'           => wp_create_nonce( 'wpshadow_dark_mode' ),
				'ajaxurl'         => admin_url( 'admin-ajax.php' ),
				'currentMode'     => $this->get_current_mode(),
				'wpColorScheme'   => $this->get_wp_color_scheme(),
				'userPreference'  => get_user_meta( get_current_user_id(), 'wpshadow_dark_mode_preference', true ),
			)
		);
	}

	/**
	 * Get current dark mode setting.
	 *
	 * @since 1.0.0
	 * @return string 'auto', 'light', or 'dark'
	 */
	private function get_current_mode(): string {
		$preference = get_user_meta( get_current_user_id(), 'wpshadow_dark_mode_preference', true );
		
		if ( empty( $preference ) || 'auto' === $preference ) {
			// Auto mode - check WordPress color scheme
			$wp_scheme = $this->get_wp_color_scheme();
			return in_array( $wp_scheme, array( 'midnight', 'ectoplasm', 'coffee' ), true ) ? 'dark' : 'light';
		}

		return is_string( $preference ) ? $preference : 'auto';
	}

	/**
	 * Get WordPress user color scheme.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function get_wp_color_scheme(): string {
		$user_id = get_current_user_id();
		$scheme  = get_user_meta( $user_id, 'admin_color', true );
		return is_string( $scheme ) && ! empty( $scheme ) ? $scheme : 'fresh';
	}

	/**
	 * AJAX handler to set dark mode preference.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_set_dark_mode(): void {
		check_ajax_referer( 'wpshadow_dark_mode', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$mode = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : 'auto';

		if ( ! in_array( $mode, array( 'auto', 'light', 'dark' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid mode.', 'wpshadow' ) ) );
		}

		update_user_meta( get_current_user_id(), 'wpshadow_dark_mode_preference', $mode );

		wp_send_json_success(
			array(
				'mode'    => $mode,
				'message' => __( 'Dark mode preference saved.', 'wpshadow' ),
			)
		);
	}

	/**
	 * Add dark mode toggle to admin bar.
	 *
	 * @since 1.0.0
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_toggle( $wp_admin_bar ): void {
		// Only on WPShadow pages
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'wpshadow' ) ) {
			return;
		}

		$current_mode = $this->get_current_mode();
		$icon         = 'dark' === $current_mode ? '🌙' : '☀️';

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wpshadow-dark-mode-toggle',
				'title' => $icon . ' ' . __( 'Dark Mode', 'wpshadow' ),
				'href'  => '#',
				'meta'  => array(
					'class' => 'wpshadow-dark-mode-toggle',
				),
			)
		);
	}

	/**
	 * Get default enabled state.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function get_default_enabled(): bool {
		return false; // Opt-in feature
	}
}
