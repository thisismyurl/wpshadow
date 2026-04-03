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
 * Enqueue a script only when the asset file exists.
 *
 * @param string $handle       Script handle.
 * @param string $relative_url Relative asset path from plugin root.
 * @param array  $deps         Optional dependencies.
 * @param bool   $in_footer    Optional. Load in footer.
 * @return bool True when enqueued.
 */
function wpshadow_enqueue_script_if_exists( $handle, $relative_url, $deps = array(), $in_footer = true ) {
	$asset_path = WPSHADOW_PATH . ltrim( (string) $relative_url, '/' );
	if ( ! file_exists( $asset_path ) ) {
		return false;
	}

	$deps = array_values(
		array_filter(
			(array) $deps,
			static function ( $dep ): bool {
				return is_string( $dep ) && '' !== $dep && wp_script_is( $dep, 'registered' );
			}
		)
	);

	wp_enqueue_script(
		$handle,
		WPSHADOW_URL . ltrim( (string) $relative_url, '/' ),
		$deps,
		(string) filemtime( $asset_path ),
		$in_footer
	);

	return true;
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

	// Workflow and Guardian screens currently rely on the shared admin bundles only.
}

/**
 * Enqueue mobile friendliness checker assets.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_mobile_friendliness_assets( $hook ) {
	if ( strpos( $hook, 'wpshadow-reports' ) === false ) {
		return;
	}

	$tool = Form_Param_Helper::get( 'tool', 'key', '' );
	if ( $tool !== 'mobile-friendliness' ) {
		return;
	}

	// Mobile friendliness reports currently use the shared admin styling only.
}

/**
 * Enqueue accessibility audit assets.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_a11y_audit_assets( $hook ) {
	if ( strpos( $hook, 'wpshadow-reports' ) === false ) {
		return;
	}

	$tool = Form_Param_Helper::get( 'tool', 'key', '' );
	if ( $tool !== 'a11y-audit' ) {
		return;
	}

	// Accessibility audit reports currently use the shared admin styling only.
}

/**
 * Enqueue broken links checker assets.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_broken_links_assets( $hook ) {
	if ( strpos( $hook, 'wpshadow-reports' ) === false ) {
		return;
	}

	$tool = Form_Param_Helper::get( 'tool', 'key', '' );
	if ( $tool !== 'broken-links' ) {
		return;
	}

	// Broken links reports currently use the shared admin styling only.
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

	// Site Health explanations currently use the shared admin styling only.
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
		// Dedicated dark mode stylesheet is not shipped in the current build.
	}
}

/**
 * Enqueue tooltip assets.
 *
 * @return void
 */
function wpshadow_enqueue_tooltip_assets() {
	global $pagenow;

	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || strpos( (string) $screen->id, 'wpshadow' ) === false ) {
		return;
	}

	// Skip tooltips on specific pages
	if ( in_array( $pagenow, array( 'edit-comments.php', 'edit.php' ), true ) ) {
		return;
	}

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	// Ensure tooltip helper functions are loaded before calling them.
	if ( ! function_exists( 'wpshadow_get_user_tip_prefs' ) || ! function_exists( 'wpshadow_get_tooltip_catalog' ) ) {
		$tooltip_helpers = WPSHADOW_PATH . 'includes/systems/dashboard/widgets/class-tooltip-manager.php';
		if ( file_exists( $tooltip_helpers ) ) {
			require_once $tooltip_helpers;
		}
	}

	if ( ! function_exists( 'wpshadow_get_user_tip_prefs' ) || ! function_exists( 'wpshadow_get_tooltip_catalog' ) ) {
		return;
	}

	// Tooltip styling is handled by the shared admin bundles in the current build.
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
 * Enqueue report builder and renderer assets.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function wpshadow_enqueue_report_assets( $hook ) {
	if ( strpos( $hook, 'wpshadow-reports' ) === false && strpos( $hook, 'wpshadow-report' ) === false ) {
		return;
	}

	// Report pages currently use shared admin styling only.
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

	// Inline helper styles were consolidated into the shared admin stylesheet.
}

/**
 * Register all asset enqueuing hooks.
 *
 * @return void
 */
function wpshadow_register_asset_hooks() {
	// Main page assets
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_workflow_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_mobile_friendliness_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_a11y_audit_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_broken_links_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_site_health_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_tooltip_assets' );

	// Auto-extracted inline styles
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_inline_styles_css' );

	// New consolidated asset enqueuing
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_admin_pages_assets' );
	add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_report_assets' );
}

wpshadow_register_asset_hooks();
