<?php
/**
 * Asset Manager for WPShadow
 *
 * Centralized CSS/JS enqueuing for all WPShadow pages.
 *
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue workflow assets.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_workflow_assets( $hook ) {
	if ( strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}
	
	// Workflow list scripts
	if ( $hook === 'toplevel_page_wpshadow' || strpos( $hook, 'wpshadow-workflows' ) !== false ) {
		wp_enqueue_script(
			'wpshadow-workflow-list',
			WPSHADOW_URL . 'assets/js/workflow-list.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);
		
		wp_localize_script( 'wpshadow-workflow-list', 'wpshadowWorkflow', array(
			'nonce' => wp_create_nonce( 'wpshadow_workflow' ),
		) );
	}

	// Guardian Dashboard and Settings assets (Phase 8)
	if ( strpos( $hook, 'wpshadow-guardian' ) !== false ) {
		wp_enqueue_style(
			'wpshadow-guardian-dashboard-settings',
			WPSHADOW_URL . 'assets/css/guardian-dashboard-settings.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-guardian-dashboard-settings',
			WPSHADOW_URL . 'assets/js/guardian-dashboard-settings.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		// Localize script for AJAX
		wp_localize_script( 'wpshadow-guardian-dashboard-settings', 'wpshadow', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wpshadow_guardian_nonce' )
		) );
	}
}

/**
 * Enqueue color contrast checker assets.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_color_contrast_assets( $hook ) {
	if ( strpos( $hook, 'wpshadow-tools' ) === false ) {
		return;
	}

	$tool = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : '';
	if ( $tool !== 'color-contrast' ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-color-contrast',
		WPSHADOW_URL . 'assets/css/color-contrast.css',
		array(),
		WPSHADOW_VERSION
	);

	wp_enqueue_script(
		'wpshadow-color-contrast',
		WPSHADOW_URL . 'assets/js/color-contrast.js',
		array(),
		WPSHADOW_VERSION,
		true
	);

	wp_localize_script( 'wpshadow-color-contrast', 'wpshadowContrast', array(
		'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
		'themeNonce'     => wp_create_nonce( 'wpshadow_theme_contrast' ),
		'i18nInvalid'    => __( 'Please enter valid 6-digit hex colors.', 'wpshadow' ),
		'i18nPass'       => __( 'Pass', 'wpshadow' ),
		'i18nFail'       => __( 'Fail', 'wpshadow' ),
		'i18nRatioLabel' => __( 'Contrast ratio', 'wpshadow' ),
		'i18nThemeScan'  => __( 'Scan Active Theme', 'wpshadow' ),
		'i18nThemeError' => __( 'Unable to scan the active theme. Please try again.', 'wpshadow' ),
		'i18nThemeBg'    => __( 'Background', 'wpshadow' ),
	) );
}

/**
 * Enqueue mobile friendliness checker assets.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_mobile_friendliness_assets( $hook ) {
	if ( strpos( $hook, 'wpshadow-tools' ) === false ) {
		return;
	}

	$tool = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : '';
	if ( $tool !== 'mobile-friendliness' ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-mobile-friendliness',
		WPSHADOW_URL . 'assets/css/mobile-friendliness.css',
		array(),
		WPSHADOW_VERSION
	);

	wp_enqueue_script(
		'wpshadow-mobile-friendliness',
		WPSHADOW_URL . 'assets/js/mobile-friendliness.js',
		array(),
		WPSHADOW_VERSION,
		true
	);

	wp_localize_script( 'wpshadow-mobile-friendliness', 'wpshadowMobileCheck', array(
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'wpshadow_mobile_check' ),
		'defaultUrl' => home_url(),
		'i18nError'  => __( 'Unable to complete the mobile check. Please try again.', 'wpshadow' ),
		'i18nRun'    => __( 'Run Mobile Check', 'wpshadow' ),
		'i18nRunning' => __( 'Checking...', 'wpshadow' ),
	) );
}

/**
 * Enqueue site health explanations CSS.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_site_health_assets( $hook ) {
	// Site Health page is 'site-health.php' or in Tools menu
	if ( $hook !== 'site-health.php' && strpos( $hook, 'tools.php' ) === false ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-site-health-explanations',
		WPSHADOW_URL . 'assets/css/site-health-explanations.css',
		array(),
		WPSHADOW_VERSION
	);
}

/**
 * Enqueue dark mode CSS.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_dark_mode_assets( $hook ) {
	if ( strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	$dark_mode_pref = get_user_meta( $user_id, 'wpshadow_dark_mode_preference', true ) ?: 'auto';

	if ( 'light' === $dark_mode_pref ) {
		return;
	}

	if ( 'dark' === $dark_mode_pref || 'auto' === $dark_mode_pref ) {
		wp_enqueue_style(
			'wpshadow-dark-mode',
			WPSHADOW_URL . 'assets/css/dark-mode.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-dark-mode-script',
			WPSHADOW_URL . 'assets/js/dark-mode.js',
			array(),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script( 'wpshadow-dark-mode-script', 'wpshadowDarkMode', array(
			'pref' => $dark_mode_pref,
		) );
	}
}

/**
 * Enqueue tooltip assets.
 *
 * @return void
 */
function wpshadow_enqueue_tooltip_assets() {
	global $pagenow;
	
	// Skip tooltips on specific pages
	if ( in_array( $pagenow, array( 'plugins.php', 'edit-comments.php', 'edit.php' ), true ) ) {
		return;
	}
	
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	// Enqueue tooltip CSS
	wp_enqueue_style(
		'wpshadow-tooltips',
		WPSHADOW_URL . 'assets/css/tooltips.css',
		array(),
		WPSHADOW_VERSION
	);

	// Enqueue tooltip JS
	wp_enqueue_script(
		'wpshadow-tooltips',
		WPSHADOW_URL . 'assets/js/tooltips.js',
		array(),
		WPSHADOW_VERSION,
		false
	);

	// Get user preferences
	$prefs = wpshadow_get_user_tip_prefs( $user_id );
	$disabled_categories = $prefs['disabled_categories'] ?? array();
	$dismissed_tips = $prefs['dismissed_tips'] ?? array();

	// Get full tooltip catalog
	$catalog = wpshadow_get_tooltip_catalog();

	// Build tooltip data object, excluding admin bar tooltips
	$tooltip_data = array();
	foreach ( $catalog as $tip ) {
		// Skip admin bar tooltips
		if ( strpos( $tip['selector'], '#wp-admin-bar-' ) === 0 ) {
			continue;
		}
		
		$tooltip_data[ $tip['id'] ] = array(
			'id'       => $tip['id'],
			'selector' => $tip['selector'],
			'title'    => $tip['title'],
			'message'  => $tip['message'],
			'category' => $tip['category'],
			'level'    => $tip['level'],
			'kb_url'   => ! empty( $tip['kb_url'] ) ? $tip['kb_url'] : '',  // Include KB URL if available
		);
	}

	// Localize tooltip data
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowTooltips', $tooltip_data );
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowDisabledTipCategories', $disabled_categories );
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowDismissedTips', $dismissed_tips );
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowTipNonce', array( 'nonce' => wp_create_nonce( 'wpshadow_tip_dismiss' ) ) );
}

/**
 * Register all asset enqueuing hooks.
 *
 * @return void
 */
function wpshadow_register_asset_hooks() {
	// Main page assets
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_workflow_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_color_contrast_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_mobile_friendliness_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_site_health_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_dark_mode_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_tooltip_assets' );
}

wpshadow_register_asset_hooks();
