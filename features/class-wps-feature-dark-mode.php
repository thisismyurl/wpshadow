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
				'name'               => __( 'Dark Mode', 'wpshadow' ),
				'description_short'  => __( 'Switch your WordPress admin to a dark theme', 'wpshadow' ),
				'description_long'   => __( 'Changes your WordPress admin area to use dark colors instead of bright white. Easier on your eyes, especially when working late at night or in dimly lit rooms. Can automatically match your computer\'s dark mode setting, or you can turn it on and off with a simple button. Great for reducing eye strain during long work sessions.', 'wpshadow' ),
				'description_wizard' => __( 'Make WordPress admin pages use dark colors to be easier on your eyes. Perfect for working at night or if you find bright white screens uncomfortable.', 'wpshadow' ),
				'aliases'            => array( 'dark theme', 'night mode', 'dark admin', 'admin theme', 'eye strain', 'ui theme' ),
				'sub_features'       => array(
					'respect_system_preference' => array(
						'name'               => __( 'Match Computer Setting', 'wpshadow' ),
						'description_short'  => __( 'Automatically use your computer\'s dark mode choice', 'wpshadow' ),
						'description_long'   => __( 'WordPress will automatically switch between light and dark colors based on your computer\'s settings. If you have dark mode turned on in Windows, Mac, or Linux, WordPress will match it. Changes happen instantly when you update your computer\'s theme, no need to reload the page.', 'wpshadow' ),
						'description_wizard' => __( 'Let WordPress automatically match whatever dark mode setting you have on your computer.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'user_override'             => array(
						'name'               => __( 'Manual On/Off Switch', 'wpshadow' ),
						'description_short'  => __( 'Add a button to turn dark mode on or off yourself', 'wpshadow' ),
						'description_long'   => __( 'Adds an easy button at the top of your WordPress admin that lets you switch between light mode, dark mode, or automatic. Your choice is remembered every time you log in. Useful if you want WordPress in dark mode but the rest of your computer in light mode, or the other way around. Each person who uses WordPress can have their own preference.', 'wpshadow' ),
						'description_wizard' => __( 'Add a simple button that lets you or your team members choose light or dark mode for WordPress, separate from your computer settings.', 'wpshadow' ),
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
