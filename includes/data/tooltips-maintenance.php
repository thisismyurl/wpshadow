<?php
/**
 * Tooltip Catalog: Maintenance
 * 
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	[
		'id' => 'nav-dashboard-updates',
		'selector' => '#menu-dashboard li a[href*="update-core.php"]',
		'title' => __( 'Updates', 'wpshadow' ),
		'message' => __( 'Check and install updates for WordPress, plugins, and themes to keep your site secure and running smoothly.', 'wpshadow' ),
		'category' => 'maintenance',
		'level' => 'beginner',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/general-updates',
	],
	[
		'id' => 'nav-tools-site-health',
		'selector' => '#menu-tools li a[href*="site-health.php"]',
		'title' => __( 'Site health', 'wpshadow' ),
		'message' => __( 'Check your site status and get recommendations for improvements.', 'wpshadow' ),
		'category' => 'maintenance',
		'level' => 'intermediate',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/general-site-health',
	],
	[
		'id' => 'bar-updates',
		'selector' => '#wp-admin-bar-updates',
		'title' => __( 'Updates available', 'wpshadow' ),
		'message' => __( 'Install WordPress, plugin, and theme updates to stay secure.', 'wpshadow' ),
		'category' => 'maintenance',
		'level' => 'intermediate',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/general-updates-available',
	],
];
