<?php
/**
 * Plugin Name:         WPShadow Pro
 * Plugin URI:          https://wpshadow.com/
 * Description:         Professional features for WPShadow. Requires WPShadow Core.
 * Version:             1.2601.75000
 * Requires Plugin:     wpshadow
 * Requires at least:   6.4
 * Requires PHP:        8.1.29
 * Author:              WPShadow Team
 * License:             GPL2
 * Text Domain:         wpshadow-pro
 * Domain Path:         /languages
 * @package WPShadow\Pro
 */

declare(strict_types=1);

namespace WPShadow\Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// =====================================================================
// STEP 1: Verify wpshadow.php is active
// =====================================================================
if ( ! defined( 'WPSHADOW_PATH' ) || ! is_plugin_active( 'wpshadow/wpshadow.php' ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			echo '<div class="notice notice-error is-dismissible"><p>';
			echo '<strong>WPShadow Pro:</strong> This plugin requires WPShadow Core to be active. ';
			echo 'Please install and activate WPShadow before using WPShadow Pro.';
			echo '</p></div>';
		}
	);
	return; // Exit early, don't load Pro
}

// =====================================================================
// STEP 2: Define Pro constants
// =====================================================================
if ( ! defined( 'WPSHADOW_PRO_VERSION' ) ) {
	define( 'WPSHADOW_PRO_VERSION', '1.2601.75000' );
}
if ( ! defined( 'WPSHADOW_PRO_FILE' ) ) {
	define( 'WPSHADOW_PRO_FILE', __FILE__ );
}
if ( ! defined( 'WPSHADOW_PRO_PATH' ) ) {
	define( 'WPSHADOW_PRO_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WPSHADOW_PRO_URL' ) ) {
	define( 'WPSHADOW_PRO_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'WPSHADOW_PRO_BASENAME' ) ) {
	define( 'WPSHADOW_PRO_BASENAME', plugin_basename( __FILE__ ) );
}

// =====================================================================
// STEP 3: Hook Pro feature registration into Core plugin
// =====================================================================
add_action( 'wpshadow_register_features', __NAMESPACE__ . '\\load_pro_features', 20 );

/**
 * Load all Pro feature files and register them
 *
 * @return void
 */
function load_pro_features(): void {
	// ====================================================================
	// LICENSE LEVEL 3+ FEATURES (27 Paid Features)
	// ====================================================================
	// Base classes (shared with Core)
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/interface-wps-feature.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-abstract.php' );

	// License Level 3 (Business - 11 features)
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-asset-minification.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-brute-force-protection.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-cdn-integration.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-conditional-loading.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-critical-css.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-database-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-hardening.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-image-optimizer.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-page-cache.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-script-deferral.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-script-optimizer.php' );

	// License Level 4 (Professional - 10 features)
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-conflict-sandbox.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-firewall.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-malware-scanner.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-performance-alerts.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-troubleshooting-mode.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-uptime-monitor.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-visual-regression.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-vulnerability-watch.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-weekly-performance-report.php' );

	// License Level 5 (Enterprise - 6 features)
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-auto-rollback.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-customization-audit.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-image-smart-focus.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-smart-recommendations.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-traffic-monitor.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-two-factor-auth.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'features/class-wps-feature-vault-audit.php' );

	// ====================================================================
	// REGISTER PRO FEATURES
	// ====================================================================

	// License Level 3 features
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Asset_Minification() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Brute_Force_Protection() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_CDN_Integration() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Conditional_Loading() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Critical_CSS() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Database_Cleanup() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Hardening() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Image_Optimizer() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Page_Cache() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Script_Deferral() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Script_Optimizer() );

	// License Level 4 features
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Conflict_Sandbox() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Firewall() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Malware_Scanner() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Performance_Alerts() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Troubleshooting_Mode() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Uptime_Monitor() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Visual_Regression() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Vulnerability_Watch() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Weekly_Performance_Report() );

	// License Level 5 features
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Auto_Rollback() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Customization_Audit() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Image_Smart_Focus() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Smart_Recommendations() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Traffic_Monitor() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Two_Factor_Auth() );
	\WPShadow\CoreSupport\register_WPSHADOW_feature( new \WPShadow\CoreSupport\WPSHADOW_Feature_Vault_Audit() );
}

// =====================================================================
// STEP 4: License verification (Pro-specific)
// =====================================================================
add_action( 'admin_init', __NAMESPACE__ . '\\verify_pro_license', 5 );

/**
 * Verify Pro license is valid and disable features if not
 *
 * @return void
 */
function verify_pro_license(): void {
	// TODO: Implement license verification
	// Check if license is valid via WPSHADOW_License class
	// If not valid, disable Pro features
	// Show admin notice if license is expired/invalid
}

// =====================================================================
// STEP 5: Pro plugin initialization
// =====================================================================
do_action( 'wpshadow_pro_loaded' );
