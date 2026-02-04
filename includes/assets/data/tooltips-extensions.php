<?php
/**
 * Tooltip Catalog: Extensions
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	array(
		'id'       => 'nav-plugins',
		'selector' => '#menu-plugins > a',
		'title'    => __( 'Extend functionality', 'wpshadow' ),
		'message'  => __( 'Browse, install, and manage plugins to add features to your site.', 'wpshadow' ),
		'category' => 'extensions',
		'level'    => 'beginner',
		'kb_url'   => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/general-extend-functionality',
	),
	array(
		'id'       => 'nav-plugins-list',
		'selector' => '#menu-plugins li a[href*="plugins.php"]',
		'title'    => __( 'Installed plugins', 'wpshadow' ),
		'message'  => __( 'See all your installed plugins, activate, deactivate, or delete them.', 'wpshadow' ),
		'category' => 'extensions',
		'level'    => 'beginner',
		'kb_url'   => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/plugins-installed-plugins',
	),
	array(
		'id'       => 'nav-plugins-add',
		'selector' => '#menu-plugins li a[href*="plugin-install.php"]',
		'title'    => __( 'Add new plugins', 'wpshadow' ),
		'message'  => __( 'Search and install plugins from the WordPress plugin directory.', 'wpshadow' ),
		'category' => 'extensions',
		'level'    => 'beginner',
		'kb_url'   => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/plugin-install-add-new-plugins',
	),
	array(
		'id'       => 'plugin-editor',
		'selector' => 'a[href*="plugin-editor.php"]',
		'title'    => __( 'Edit code', 'wpshadow' ),
		'message'  => __( 'Edit plugin or theme files directly. Be careful—errors can break your site.', 'wpshadow' ),
		'category' => 'extensions',
		'level'    => 'intermediate',
		'kb_url'   => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/plugin-editor-edit-code',
	),
	array(
		'id'       => 'plugin-activate',
		'selector' => 'span.activate > a, div.row-actions span.activate > a',
		'title'    => __( 'Activate plugin', 'wpshadow' ),
		'message'  => __( 'Turn on this plugin to enable its features on your site.', 'wpshadow' ),
		'category' => 'extensions',
		'level'    => 'intermediate',
		'kb_url'   => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/general-activate-plugin',
	),
	array(
		'id'       => 'plugin-deactivate',
		'selector' => 'span.deactivate > a, div.row-actions span.deactivate > a',
		'title'    => __( 'Deactivate plugin', 'wpshadow' ),
		'message'  => __( 'Turn off this plugin. Its features will no longer be available.', 'wpshadow' ),
		'category' => 'extensions',
		'level'    => 'intermediate',
		'kb_url'   => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/general-deactivate-plugin',
	),
	array(
		'id'       => 'nav-plugins-plugin-editor',
		'selector' => '#menu-plugins li a[href*="plugin-editor.php"]',
		'title'    => __( 'Plugin File Editor', 'wpshadow' ),
		'message'  => __( 'Edit plugin PHP files directly. Be careful—syntax errors can break your site and disable the plugin.', 'wpshadow' ),
		'category' => 'extensions',
		'level'    => 'intermediate',
		'kb_url'   => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/plugin-editor-plugin-file-editor',
	),
);
