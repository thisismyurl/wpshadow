<?php
/**
 * Security API Settings Initializer
 *
 * Registers the Security API settings page in the WordPress admin.
 *
 * @package    WPShadow
 * @subpackage Admin\Pages\SecurityAPI
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages\SecurityAPI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize Security API Settings Page
 *
 * Hook into WordPress admin to register the security API settings page.
 *
 * @since 1.6035.0000
 */
function init_security_api_settings() {
	// Only in admin
	if ( ! is_admin() ) {
		return;
	}

	// Create instance
	$page = new Security_API_Settings_Page();

	// Add settings page
	add_action( 'admin_menu', function() use ( $page ) {
		// Add submenu page under WPShadow Settings
		add_submenu_page(
			'wpshadow-settings',
			__( 'Security API Integrations', 'wpshadow' ),
			__( 'Security APIs', 'wpshadow' ),
			'manage_options',
			'wpshadow-security-api',
			function() use ( $page ) {
				$page->render();
			}
		);
	}, 20 );

	// Register settings
	add_action( 'admin_init', function() {
		// Register options for each service
		register_setting( 'wpshadow_security_apis', 'wpshadow_wpscan_enabled' );
		register_setting( 'wpshadow_security_apis', 'wpshadow_wpscan_api_key' );
		register_setting( 'wpshadow_security_apis', 'wpshadow_hibp_enabled' );
		register_setting( 'wpshadow_security_apis', 'wpshadow_abuseipdb_enabled' );
		register_setting( 'wpshadow_security_apis', 'wpshadow_abuseipdb_api_key' );
		register_setting( 'wpshadow_security_apis', 'wpshadow_gsb_enabled' );
		register_setting( 'wpshadow_security_apis', 'wpshadow_gsb_api_key' );
		register_setting( 'wpshadow_security_apis', 'wpshadow_phishtank_enabled' );
		register_setting( 'wpshadow_security_apis', 'wpshadow_phishtank_api_key' );
	} );
}

// Initialize on plugins_loaded (priority 12 = after main plugin load)
add_action( 'plugins_loaded', __NAMESPACE__ . '\init_security_api_settings', 12 );
