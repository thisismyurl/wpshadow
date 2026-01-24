<?php
/**
 * Plugin Name: WPShadow Site
 * Description: Lightweight starter plugin scaffold for WPShadow-branded sites.
 * Version: 0.1.0
 * Author: WPShadow
 * License: GPL-2.0-or-later
 * Text Domain: wpshadow-site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Basic constants.
define( 'WPSHADOW_SITE_VERSION', '0.1.0' );
define( 'WPSHADOW_SITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSHADOW_SITE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Register admin menu page.
 */
function wpshadow_site_register_menu() {
	add_menu_page(
		__( 'WPShadow Site', 'wpshadow-site' ),
		__( 'WPShadow Site', 'wpshadow-site' ),
		'manage_options',
		'wpshadow-site',
		'wpshadow_site_render_page',
		'dashicons-shield-alt',
		82
	);
}
add_action( 'admin_menu', 'wpshadow_site_register_menu' );

/**
 * Enqueue admin assets for the plugin page only.
 */
function wpshadow_site_admin_assets( $hook ) {
	if ( 'toplevel_page_wpshadow-site' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-site-admin',
		WPSHADOW_SITE_URL . 'assets/admin.css',
		array(),
		WPSHADOW_SITE_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'wpshadow_site_admin_assets' );

/**
 * Render the admin page.
 */
function wpshadow_site_render_page() {
	?>
	<div class="wrap wpshadow-site">
		<h1><?php esc_html_e( 'WPShadow Site', 'wpshadow-site' ); ?></h1>
		<p><?php esc_html_e( 'Welcome to your WPShadow site plugin scaffold. Add your custom admin UI or integrations here.', 'wpshadow-site' ); ?></p>
	</div>
	<?php
}
