<?php
/**
 * Admin assets enqueue functions extracted from bootstrap.
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue admin scripts and styles.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function wpshadow_admin_enqueue( string $hook ): void {
	// Load on all wpshadow related pages (core, hubs, spokes).
	// Hooks can be: toplevel_page_wpshadow, support-hub_page_wpshadow-hub-media, etc.
	if ( false === strpos( $hook, 'wpshadow' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	// Cache-bust using current timestamp to force reload for testing.
	$cache_bust = time();

	// Enqueue modern design system (shared across all WPS plugins).
	wp_enqueue_style(
		'wps-ui-system',
		WPSHADOW_URL . 'assets/css/wps-ui-system.css',
		array(),
		$cache_bust
	);

	wp_enqueue_style(
		'wps-core-admin',
		WPSHADOW_URL . 'assets/css/admin.css',
		array( 'wps-ui-system' ),
		$cache_bust
	);

	wp_enqueue_style(
		'wps-tab-navigation',
		WPSHADOW_URL . 'assets/css/tab-navigation.css',
		array( 'wps-ui-system' ),
		$cache_bust
	);

	// Get context once for reuse.
	$context = WPSHADOW_Tab_Navigation::get_current_context();
	$tab     = $context['tab'] ?? '';

	// Enqueue help styles when on help tab.
	if ( 'help' === $tab ) {
		wp_enqueue_style(
			'wps-help',
			WPSHADOW_URL . 'assets/css/help.css',
			array( 'wps-ui-system' ),
			$cache_bust
		);
	}
	// Enqueue responsive design system (mobile-first, touch-friendly).
	wp_enqueue_style(
		'wps-responsive',
		WPSHADOW_URL . 'assets/css/responsive.css',
		array( 'wps-ui-system', 'wps-core-admin' ),
		$cache_bust
	);

	// Enable drag and drop for dashboard metaboxes on all wpshadow pages using WordPress native postboxes.
	// EXCLUDE features tab due to History API SecurityError on GitHub Codespaces
	$current_tab = isset( $_GET['wpshadow_tab'] ) ? sanitize_key( (string) $_GET['wpshadow_tab'] ) : '';
	if ( $screen && false !== strpos( $screen->id, 'wpshadow' ) && 'features' !== $current_tab ) {
		// Use WordPress's built-in postbox drag and drop.
		wp_enqueue_script( 'postbox' );

		// Enqueue Chart.js for performance history visualization on all wpshadow pages.
		wp_enqueue_script(
			'chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
			array(),
			'4.4.1',
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		// Add SRI integrity check for CDN security.
		add_filter(
			'script_loader_tag',
			function ( $tag, $handle ) {
				if ( 'chartjs' === $handle ) {
					$tag = str_replace(
						' src=',
						' integrity="sha256-R13eauWpfoMV/hlminLUepT/V0UAXp3cy6/KiBnecnw=" crossorigin="anonymous" src=',
						$tag
					);
				}
				return $tag;
			},
			10,
			2
		);

		// Add custom script to handle context-specific state saving.
		wp_enqueue_script(
			'wps-postbox-state',
			WPSHADOW_URL . 'assets/js/postbox-state.js',
			array( 'jquery', 'postbox' ),
			$cache_bust,
			true
		);

		// Use cached context from above for unique state key.
		$hub_id    = $context['hub'] ?? '';
		$state_key = 'wpshadow' . ( $hub_id ? '-' . $hub_id : '' );

		wp_localize_script(
			'wps-postbox-state',
			'wpsPostboxState',
			array(
				'stateKey' => $state_key,
				'nonce'    => wp_create_nonce( 'wpshadow_postbox_state' ),
			)
		);

		wp_enqueue_style(
			'wps-dashboard-drag',
			WPSHADOW_URL . 'assets/css/dashboard-drag.css',
			array(),
			$cache_bust
		);
	} else {

	}

	wp_enqueue_script(
		'wps-core-admin',
		WPSHADOW_URL . 'assets/js/admin.js',
		array( 'jquery' ),
		$cache_bust,
		true
	);

	// Enqueue responsive navigation script.
	wp_enqueue_script(
		'wps-responsive-nav',
		WPSHADOW_URL . 'assets/js/responsive-nav.js',
		array( 'jquery' ),
		$cache_bust,
		true
	);

	// Localize script for AJAX and i18n.
	wp_localize_script(
		'wps-core-admin',
		'wpsAdminData',
		array(
			'toggleNonce' => wp_create_nonce( 'wpshadow_toggle_module' ),
			'actionNonce' => wp_create_nonce( 'wpshadow_module_action' ),
			'i18n'        => array(
				'enabled'      => __( 'Enabled', 'plugin-wpshadow' ),
				'disabled'     => __( 'Disabled', 'plugin-wpshadow' ),
				'ajaxError'    => __( 'An error occurred. Please try again.', 'plugin-wpshadow' ),
				'noResults'    => __( 'No modules match this filter.', 'plugin-wpshadow' ),
				'installFirst' => __( 'Install the module before enabling it.', 'plugin-wpshadow' ),
				'installing'   => __( 'Installing...', 'plugin-wpshadow' ),
				'updating'     => __( 'Updating...', 'plugin-wpshadow' ),
				'install'      => __( 'Install', 'plugin-wpshadow' ),
				'update'       => __( 'Update', 'plugin-wpshadow' ),
			),
		)
	);

	// Enqueue module actions script (install/update/activate).
	wp_enqueue_script(
		'wps-module-actions',
		WPSHADOW_URL . 'assets/js/module-actions.js',
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
			'nonce'   => wp_create_nonce( 'wpshadow_module_actions' ),
		)
	);

	// Register debug tools assets (enqueued conditionally in debug-tools.php).
	wp_register_style(
		'wps-debug-tools',
		WPSHADOW_URL . 'assets/css/debug-tools.css',
		array(),
		$cache_bust
	);

	wp_register_script(
		'wps-debug-tools',
		WPSHADOW_URL . 'assets/js/debug-tools.js',
		array( 'jquery' ),
		$cache_bust,
		true
	);
	// Enqueue Spoke Collection assets if on collection tab.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_tab = isset( $_GET['wpshadow_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['wpshadow_tab'] ) ) : 'dashboard';
	if ( 'collection' === $current_tab ) {
		wp_enqueue_style(
			'wps-spoke-collection',
			WPSHADOW_URL . 'assets/css/spoke-collection.css',
			array( 'wps-ui-system' ),
			$cache_bust
		);

		wp_enqueue_script(
			'wps-spoke-collection',
			WPSHADOW_URL . 'assets/js/spoke-collection.js',
			array( 'jquery' ),
			$cache_bust,
			true
		);

		// Localize spoke collection script.
		wp_localize_script(
			'wps-spoke-collection',
			'wpsSpokeCollection',
			array(
				'nonce' => wp_create_nonce( 'wpshadow_spoke_collection' ),
				'i18n'  => array(
					'install'           => __( 'Install This Spoke', 'plugin-wpshadow' ),
					'activate'          => __( 'Activate', 'plugin-wpshadow' ),
					'deactivate'        => __( 'Deactivate', 'plugin-wpshadow' ),
					'notInstalled'      => __( 'Not Installed', 'plugin-wpshadow' ),
					'readyToActivate'   => __( 'Ready to Activate', 'plugin-wpshadow' ),
					'activeProcessing'  => __( 'Active & Processing', 'plugin-wpshadow' ),
					'mastered'          => __( 'Mastered!', 'plugin-wpshadow' ),
					'confirmDeactivate' => __( 'Are you sure you want to deactivate this spoke?', 'plugin-wpshadow' ),
				),
			)
		);
	}
}
