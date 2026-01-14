<?php
/**
 * Admin assets enqueue functions extracted from bootstrap.
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue admin scripts and styles.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function wp_support_admin_enqueue( string $hook ): void {
	// Load on all wp-support related pages (core, hubs, spokes).
	// Hooks can be: toplevel_page_wp-support, support-hub_page_wp-support-hub-media, etc.
	if ( false === strpos( $hook, 'wp-support' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	// Cache-bust using current timestamp to force reload for testing.
	$cache_bust = time();

	// Enqueue modern design system (shared across all WPS plugins).
	wp_enqueue_style(
		'wps-ui-system',
		wp_support_URL . 'assets/css/wps-ui-system.css',
		array(),
		$cache_bust
	);

	wp_enqueue_style(
		'wps-core-admin',
		wp_support_URL . 'assets/css/admin.css',
		array( 'wps-ui-system' ),
		$cache_bust
	);

	wp_enqueue_style(
		'wps-tab-navigation',
		wp_support_URL . 'assets/css/tab-navigation.css',
		array( 'wps-ui-system' ),
		$cache_bust
	);

	// Get context once for reuse.
	$context = WPS_Tab_Navigation::get_current_context();
	$tab     = $context['tab'] ?? '';

	// Enqueue help styles when on help tab.
	if ( 'help' === $tab ) {
		wp_enqueue_style(
			'wps-help',
			wp_support_URL . 'assets/css/help.css',
			array( 'wps-ui-system' ),
			$cache_bust
		);
	}
	// Enqueue responsive design system (mobile-first, touch-friendly).
	wp_enqueue_style(
		'wps-responsive',
		wp_support_URL . 'assets/css/responsive.css',
		array( 'wps-ui-system', 'wps-core-admin' ),
		$cache_bust
	);

	// Enable drag and drop for dashboard metaboxes on all wp-support pages using WordPress native postboxes.
	if ( $screen && false !== strpos( $screen->id, 'wp-support' ) ) {
		// Use WordPress's built-in postbox drag and drop.
		wp_enqueue_script( 'postbox' );

		// Enqueue Chart.js for performance history visualization (dashboard only).
		if ( 'dashboard' === $tab ) {
			wp_enqueue_script(
				'chartjs',
				'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
				array(),
				'4.4.1',
				true
			);
		}

		// Add custom script to handle context-specific state saving.
		wp_enqueue_script(
			'wps-postbox-state',
			wp_support_URL . 'assets/js/postbox-state.js',
			array( 'jquery', 'postbox' ),
			$cache_bust,
			true
		);

		// Use cached context from above for unique state key.
		$hub_id    = $context['hub'] ?? '';
		$state_key = 'wp-support' . ( $hub_id ? '-' . $hub_id : '' );

		wp_localize_script(
			'wps-postbox-state',
			'wpsPostboxState',
			array(
				'stateKey' => $state_key,
				'nonce'    => wp_create_nonce( 'WPS_postbox_state' ),
			)
		);

		wp_enqueue_style(
			'wps-dashboard-drag',
			wp_support_URL . 'assets/css/dashboard-drag.css',
			array(),
			$cache_bust
		);
	} else {

	}

	wp_enqueue_script(
		'wps-core-admin',
		wp_support_URL . 'assets/js/admin.js',
		array( 'jquery' ),
		$cache_bust,
		true
	);

	// Enqueue responsive navigation script.
	wp_enqueue_script(
		'wps-responsive-nav',
		wp_support_URL . 'assets/js/responsive-nav.js',
		array( 'jquery' ),
		$cache_bust,
		true
	);

	// Localize script for AJAX and i18n.
	wp_localize_script(
		'wps-core-admin',
		'wpsAdminData',
		array(
			'toggleNonce' => wp_create_nonce( 'WPS_toggle_module' ),
			'actionNonce' => wp_create_nonce( 'WPS_module_action' ),
			'i18n'        => array(
				'enabled'      => __( 'Enabled', 'plugin-wp-support-thisismyurl' ),
				'disabled'     => __( 'Disabled', 'plugin-wp-support-thisismyurl' ),
				'ajaxError'    => __( 'An error occurred. Please try again.', 'plugin-wp-support-thisismyurl' ),
				'noResults'    => __( 'No modules match this filter.', 'plugin-wp-support-thisismyurl' ),
				'installFirst' => __( 'Install the module before enabling it.', 'plugin-wp-support-thisismyurl' ),
				'installing'   => __( 'Installing...', 'plugin-wp-support-thisismyurl' ),
				'updating'     => __( 'Updating...', 'plugin-wp-support-thisismyurl' ),
				'install'      => __( 'Install', 'plugin-wp-support-thisismyurl' ),
				'update'       => __( 'Update', 'plugin-wp-support-thisismyurl' ),
			),
		)
	);

	// Enqueue module actions script (install/update/activate).
	wp_enqueue_script(
		'wps-module-actions',
		wp_support_URL . 'assets/js/module-actions.js',
		array(),
		$cache_bust,
		true
	);

	// Localize module actions script with nonce and AJAX URL.
	wp_localize_script(
		'wps-module-actions',
		'wpsModuleActions',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'WPS_module_actions' ),
		)
	);

	// Register debug tools assets (enqueued conditionally in debug-tools.php).
	wp_register_style(
		'wps-debug-tools',
		wp_support_URL . 'assets/css/debug-tools.css',
		array(),
		$cache_bust
	);

	wp_register_script(
		'wps-debug-tools',
		wp_support_URL . 'assets/js/debug-tools.js',
		array( 'jquery' ),
		$cache_bust,
		true
	);
	// Enqueue Spoke Collection assets if on collection tab.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_tab = isset( $_GET['WPS_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['WPS_tab'] ) ) : 'dashboard';
	if ( 'collection' === $current_tab ) {
		wp_enqueue_style(
			'wps-spoke-collection',
			wp_support_URL . 'assets/css/spoke-collection.css',
			array( 'wps-ui-system' ),
			$cache_bust
		);

		wp_enqueue_script(
			'wps-spoke-collection',
			wp_support_URL . 'assets/js/spoke-collection.js',
			array( 'jquery' ),
			$cache_bust,
			true
		);

		// Localize spoke collection script.
		wp_localize_script(
			'wps-spoke-collection',
			'wpsSpokeCollection',
			array(
				'nonce' => wp_create_nonce( 'WPS_spoke_collection' ),
				'i18n'  => array(
					'install'           => __( 'Install This Spoke', 'plugin-wp-support-thisismyurl' ),
					'activate'          => __( 'Activate', 'plugin-wp-support-thisismyurl' ),
					'deactivate'        => __( 'Deactivate', 'plugin-wp-support-thisismyurl' ),
					'notInstalled'      => __( 'Not Installed', 'plugin-wp-support-thisismyurl' ),
					'readyToActivate'   => __( 'Ready to Activate', 'plugin-wp-support-thisismyurl' ),
					'activeProcessing'  => __( 'Active & Processing', 'plugin-wp-support-thisismyurl' ),
					'mastered'          => __( 'Mastered!', 'plugin-wp-support-thisismyurl' ),
					'confirmDeactivate' => __( 'Are you sure you want to deactivate this spoke?', 'plugin-wp-support-thisismyurl' ),
				),
			)
		);
	}
}
