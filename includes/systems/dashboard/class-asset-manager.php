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

use WPShadow\Core\Form_Param_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue WPShadow admin feature assets.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_workflow_assets( $hook ) {
	if ( strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}
}


/**
 * Enqueue site health explanations CSS.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_site_health_assets( $hook ) {
	// Site Health styles should load only on the Site Health screen.
	if ( 'site-health.php' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-site-health-explanations',
		WPSHADOW_URL . 'assets/css/utilities-consolidated.css',
		array(),
		WPSHADOW_VERSION
	);
}

/**
 * Enqueue admin pages common assets.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_admin_pages_assets( $hook ) {
	if ( strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}

	// Enqueue common admin pages CSS
	wp_enqueue_style(
		'wpshadow-admin-pages',
		WPSHADOW_URL . 'assets/css/admin-pages.css',
		array( 'wpshadow-design-system' ),
		WPSHADOW_VERSION
	);

	// Enqueue common admin pages JS
	wp_enqueue_script(
		'wpshadow-admin-pages',
		WPSHADOW_URL . 'assets/js/admin-pages.js',
		array( 'jquery' ),
		WPSHADOW_VERSION,
		true
	);

	// Localize common admin data.
	\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
		'wpshadow-admin-pages',
		'wpshadowAdmin',
		'wpshadow_admin',
		array(
			'locale' => get_locale(),
			'i18n'   => array(
				'saving'        => __( 'Saving...', 'wpshadow' ),
				'saved'         => __( 'Saved successfully!', 'wpshadow' ),
				'error'         => __( 'An error occurred. Please try again.', 'wpshadow' ),
				'confirmDelete' => __( 'Are you sure you want to delete this?', 'wpshadow' ),
				'working'       => __( 'Working on it...', 'wpshadow' ),
				'workingDetails' => __( 'This can take a few minutes. You can keep this tab open while we finish.', 'wpshadow' ),
				'cancel'        => __( 'Cancel', 'wpshadow' ),
				'creatingBackup' => __( 'Creating backup...', 'wpshadow' ),
				'backupDetails'  => __( 'This can take a few minutes, depending on your site size.', 'wpshadow' ),
				'restoringBackup' => __( 'Restoring backup...', 'wpshadow' ),
				'restoreDetails' => __( 'Please keep this tab open while we restore your site.', 'wpshadow' ),
				'deletingBackup' => __( 'Deleting backup...', 'wpshadow' ),
				'deleteDetails'  => __( 'This should only take a moment.', 'wpshadow' ),
				'findReplaceRunning' => __( 'Running find and replace...', 'wpshadow' ),
				'findReplaceDetails' => __( 'We are updating your content safely.', 'wpshadow' ),
				'runningDiagnostics' => __( 'Running diagnostics...', 'wpshadow' ),
				'diagnosticsDetails' => __( 'This can take a few minutes.', 'wpshadow' ),
				'diagnosticsProgressSteps' => array(
					__( 'Reviewing site settings and visibility options', 'wpshadow' ),
					__( 'Checking titles, descriptions, and metadata', 'wpshadow' ),
					__( 'Looking at performance and accessibility', 'wpshadow' ),
					__( 'Scanning internal links and structure', 'wpshadow' ),
					__( 'Reviewing structured data and sharing tags', 'wpshadow' ),
				),
				'diagnosticsStepLabel' => __( 'Step {current} of {total}', 'wpshadow' ),
				'diagnosticsElapsedLabel' => __( 'Elapsed', 'wpshadow' ),
				'diagnosticsRemainingLabel' => __( 'Estimated remaining', 'wpshadow' ),
				'runningScan'    => __( 'Running a scan...', 'wpshadow' ),
				'scanDetails'    => __( 'We will update you when the scan is done.', 'wpshadow' ),
				'generatingDna'  => __( 'Generating site DNA...', 'wpshadow' ),
				'dnaDetails'     => __( 'We are gathering site details.', 'wpshadow' ),
				'preparingReport' => __( 'Preparing report...', 'wpshadow' ),
				'reportDetails'   => __( 'This can take a few minutes.', 'wpshadow' ),
			),
		)
	);
}


/**
 * Enqueue auto-extracted inline styles CSS files.
 * These files contain CSS classes extracted from inline styles during refactoring.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_inline_styles_css( $hook ) {
	// Only load on WPShadow admin pages
	if ( strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}

	// Enqueue color styles (backgrounds, borders, etc.)
	wp_enqueue_style(
		'wpshadow-inline-colors',
		WPSHADOW_URL . 'assets/css/wps-inline-colors.css',
		array(),
		WPSHADOW_VERSION
	);

	// Enqueue layout styles (flexbox, grid, alignment)
	wp_enqueue_style(
		'wpshadow-inline-layouts',
		WPSHADOW_URL . 'assets/css/wps-inline-layouts.css',
		array( 'wpshadow-inline-colors' ),
		WPSHADOW_VERSION
	);

	// Enqueue spacing styles (margins, padding)
	wp_enqueue_style(
		'wpshadow-inline-spacing',
		WPSHADOW_URL . 'assets/css/wps-inline-spacing.css',
		array( 'wpshadow-inline-layouts' ),
		WPSHADOW_VERSION
	);
}

/**
 * Register all asset enqueuing hooks.
 *
 * @return void
 */
function wpshadow_register_asset_hooks() {
	// Main page assets
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_workflow_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_site_health_assets' );

	// Auto-extracted inline styles
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_inline_styles_css' );

	// New consolidated asset enqueuing
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_admin_pages_assets' );
}

wpshadow_register_asset_hooks();
