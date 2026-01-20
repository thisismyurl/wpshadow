<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpshadow_admin_enqueue( string $hook ): void {

	if ( false === strpos( $hook, 'wpshadow' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	$cache_bust = time();

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

	$context = WPSHADOW_Tab_Navigation::get_current_context();
	$tab     = $context['tab'] ?? '';

	if ( 'help' === $tab ) {
		wp_enqueue_style(
			'wps-help',
			WPSHADOW_URL . 'assets/css/help.css',
			array( 'wps-ui-system' ),
			$cache_bust
		);
	}

	wp_enqueue_style(
		'wps-responsive',
		WPSHADOW_URL . 'assets/css/responsive.css',
		array( 'wps-ui-system', 'wps-core-admin' ),
		$cache_bust
	);

	$current_tab = isset( $_GET['wpshadow_tab'] ) ? sanitize_key( (string) $_GET['wpshadow_tab'] ) : '';
	if ( $screen && false !== strpos( $screen->id, 'wpshadow' ) && 'features' !== $current_tab ) {

		wp_enqueue_script( 'postbox' );

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

		wp_enqueue_script(
			'wps-postbox-state',
			WPSHADOW_URL . 'assets/js/postbox-state.js',
			array( 'jquery', 'postbox' ),
			$cache_bust,
			true
		);

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

	wp_enqueue_script(
		'wps-responsive-nav',
		WPSHADOW_URL . 'assets/js/responsive-nav.js',
		array( 'jquery' ),
		$cache_bust,
		true
	);

	wp_localize_script(
		'wps-core-admin',
		'wpsAdminData',
		array(
			'toggleNonce' => wp_create_nonce( 'wpshadow_toggle_module' ),
			'actionNonce' => wp_create_nonce( 'wpshadow_module_action' ),
			'i18n'        => array(
				'enabled'      => __( 'Enabled', 'wpshadow' ),
				'disabled'     => __( 'Disabled', 'wpshadow' ),
				'ajaxError'    => __( 'An error occurred. Please try again.', 'wpshadow' ),
				'noResults'    => __( 'No modules match this filter.', 'wpshadow' ),
				'installFirst' => __( 'Install the module before enabling it.', 'wpshadow' ),
				'installing'   => __( 'Installing...', 'wpshadow' ),
				'updating'     => __( 'Updating...', 'wpshadow' ),
				'install'      => __( 'Install', 'wpshadow' ),
				'update'       => __( 'Update', 'wpshadow' ),
			),
		)
	);

	wp_enqueue_script(
		'wps-module-actions',
		WPSHADOW_URL . 'assets/js/module-actions.js',
		array(),
		$cache_bust,
		true
	);

	wp_localize_script(
		'wps-module-actions',
		'wpsModuleActions',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wpshadow_module_actions' ),
		)
	);

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

		wp_localize_script(
			'wps-spoke-collection',
			'wpsSpokeCollection',
			array(
				'nonce' => wp_create_nonce( 'wpshadow_spoke_collection' ),
				'i18n'  => array(
					'install'           => __( 'Install This Spoke', 'wpshadow' ),
					'activate'          => __( 'Activate', 'wpshadow' ),
					'deactivate'        => __( 'Deactivate', 'wpshadow' ),
					'notInstalled'      => __( 'Not Installed', 'wpshadow' ),
					'readyToActivate'   => __( 'Ready to Activate', 'wpshadow' ),
					'activeProcessing'  => __( 'Active & Processing', 'wpshadow' ),
					'mastered'          => __( 'Mastered!', 'wpshadow' ),
					'confirmDeactivate' => __( 'Are you sure you want to deactivate this spoke?', 'wpshadow' ),
				),
			)
		);
	}
}
