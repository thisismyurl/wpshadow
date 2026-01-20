<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Dark_Mode extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'dark-mode',
				'name'            => __( 'Dark Mode Support', 'wpshadow' ),
				'description'     => __( 'Enable dark mode for WPShadow admin pages with automatic WordPress color scheme detection and manual toggle.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => false,
				'version'         => '1.0.0',
				'widget_group'    => 'accessibility',
				'sub_features'    => array(
					'respect_system_preference' => __( 'Respect System Preferences', 'wpshadow' ),
					'user_override'             => __( 'Allow User to Override Default', 'wpshadow' ),
				),
			)
		);

		$this->register_default_settings(
			array(
				'respect_system_preference' => true,
				'user_override'             => true,
			)
		);
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dark_mode_assets' ) );
		add_action( 'wp_ajax_wpshadow_set_dark_mode', array( $this, 'ajax_set_dark_mode' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	public function enqueue_dark_mode_assets(): void {
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'wpshadow' ) ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-dark-mode',
			WPSHADOW_URL . 'assets/css/dark-mode.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-dark-mode',
			WPSHADOW_URL . 'assets/js/dark-mode.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

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

	private function get_current_mode(): string {
		$preference = get_user_meta( get_current_user_id(), 'wpshadow_dark_mode_preference', true );

		if ( empty( $preference ) || 'auto' === $preference ) {
			$wp_scheme = $this->get_wp_color_scheme();
			return in_array( $wp_scheme, array( 'midnight', 'ectoplasm', 'coffee' ), true ) ? 'dark' : 'light';
		}

		return $preference;
	}

	private function get_wp_color_scheme(): string {
		$user_id = get_current_user_id();
		return get_user_meta( $user_id, 'admin_color', true ) ?: 'fresh';
	}

	public function ajax_set_dark_mode(): void {
		check_ajax_referer( 'wpshadow_dark_mode', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
			return;
		}

		$mode = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'auto';

		if ( ! in_array( $mode, array( 'auto', 'light', 'dark' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid mode.', 'wpshadow' ) ) );
			return;
		}

		update_user_meta( get_current_user_id(), 'wpshadow_dark_mode_preference', $mode );

		wp_send_json_success( array(
			'mode'    => $mode,
			'message' => __( 'Dark mode preference saved.', 'wpshadow' ),
		) );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['dark_mode'] = array(
			'label' => __( 'Dark Mode', 'wpshadow' ),
			'test'  => array( $this, 'test_dark_mode' ),
		);

		return $tests;
	}

	public function test_dark_mode(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Dark Mode', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Accessibility', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Dark mode is disabled.', 'wpshadow' ),
				'test'        => 'dark_mode',
			);
		}

		$current_mode     = $this->get_current_mode();
		$user_preference  = get_user_meta( get_current_user_id(), 'wpshadow_dark_mode_preference', true );
		$is_auto_detected = empty( $user_preference );

		return array(
			'label'       => __( 'Dark Mode Active', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Accessibility', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				__( 'Current mode: %s (%s)', 'wpshadow' ),
				ucfirst( $current_mode ),
				$is_auto_detected ? __( 'auto-detected', 'wpshadow' ) : __( 'user preference', 'wpshadow' )
			),
			'test'        => 'dark_mode',
		);
	}
}
