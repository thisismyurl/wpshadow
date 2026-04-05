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
 * Enqueue a style only when the asset file exists.
 *
 * @param string $handle       Style handle.
 * @param string $relative_url Relative asset path from plugin root.
 * @param array  $deps         Optional dependencies.
 * @return bool True when enqueued.
 */
function wpshadow_enqueue_style_if_exists( $handle, $relative_url, $deps = array() ) {
	$asset_path = WPSHADOW_PATH . ltrim( (string) $relative_url, '/' );
	if ( ! file_exists( $asset_path ) ) {
		return false;
	}

	$deps = array_values(
		array_filter(
			(array) $deps,
			static function ( $dep ): bool {
				return is_string( $dep ) && '' !== $dep && wp_style_is( $dep, 'registered' );
			}
		)
	);

	wp_enqueue_style(
		$handle,
		WPSHADOW_URL . ltrim( (string) $relative_url, '/' ),
		$deps,
		(string) filemtime( $asset_path )
	);

	return true;
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

	// Enqueue unified design system (replaces all old CSS files)
	wpshadow_enqueue_style_if_exists( 'wpshadow-system', 'assets/css/wpshadow-system.css' );

	// Enqueue dashboard JS (vanilla, no jQuery dependency).
	$dashboard_script_path = WPSHADOW_PATH . 'assets/js/wpshadow-dashboard.js';
	if ( file_exists( $dashboard_script_path ) ) {
		$dashboard_script_version = (string) filemtime( $dashboard_script_path );
		wp_enqueue_script(
			'wpshadow-dashboard',
			WPSHADOW_URL . 'assets/js/wpshadow-dashboard.js',
			array(),
			$dashboard_script_version,
			true
		);
	}

	if ( ! wp_script_is( 'wpshadow-dashboard', 'enqueued' ) ) {
		return;
	}

	// Localize common admin data.
	\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
		'wpshadow-dashboard',
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
					__( 'Looking at mobile readiness and performance', 'wpshadow' ),
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
 * Register all asset enqueuing hooks.
 *
 * @return void
 */
function wpshadow_register_asset_hooks() {
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_admin_pages_assets' );
}

wpshadow_register_asset_hooks();
