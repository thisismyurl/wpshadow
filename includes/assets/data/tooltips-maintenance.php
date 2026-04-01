<?php
/**
 * Tooltip Catalog: Maintenance
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	array(
		'id'       => 'nav-dashboard-updates',
		'selector' => '#menu-dashboard li a[href*="update-core.php"]',
		'title'    => __( 'Updates', 'wpshadow' ),
		'message'  => __( 'Check and install updates for WordPress, plugins, and themes to keep your site secure and running smoothly.', 'wpshadow' ),
		'category' => 'maintenance',
		'level'    => 'beginner',
		'kb_url'   => 'https://wpshadow.com/kb/general-updates?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_tooltips',
	),
	array(
		'id'       => 'nav-tools-site-health',
		'selector' => '#menu-tools li a[href*="site-health.php"]',
		'title'    => __( 'Site health', 'wpshadow' ),
		'message'  => __( 'Check your site status and get recommendations for improvements.', 'wpshadow' ),
		'category' => 'maintenance',
		'level'    => 'intermediate',
		'kb_url'   => 'https://wpshadow.com/kb/general-site-health?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_tooltips',
	),
	array(
		'id'       => 'bar-updates',
		'selector' => '#wp-admin-bar-updates',
		'title'    => __( 'Updates available', 'wpshadow' ),
		'message'  => __( 'Install WordPress, plugin, and theme updates to stay secure.', 'wpshadow' ),
		'category' => 'maintenance',
		'level'    => 'intermediate',
		'kb_url'   => 'https://wpshadow.com/kb/general-updates-available?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_tooltips',
	),
);
