<?php
/**
 * Tooltip Catalog: Design
 * 
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	[
		'id' => 'nav-appearance',
		'selector' => '#menu-appearance > a',
		'title' => __( 'Site design', 'wpshadow' ),
		'message' => __( 'Customize your site appearance with themes, colors, menus, and widgets.', 'wpshadow' ),
		'category' => 'design',
		'level' => 'beginner',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/general-site-design',
	],
	[
		'id' => 'nav-appearance-themes',
		'selector' => '#menu-appearance li a[href*="themes.php"]',
		'title' => __( 'Choose a theme', 'wpshadow' ),
		'message' => __( 'Browse and activate themes to change your site layout and style.', 'wpshadow' ),
		'category' => 'design',
		'level' => 'beginner',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/themes-choose-a-theme',
	],
	[
		'id' => 'nav-appearance-customize',
		'selector' => '#menu-appearance li a[href*="customize.php"]',
		'title' => __( 'Live customizer', 'wpshadow' ),
		'message' => __( 'Preview changes to colors, fonts, and layout in real-time before publishing.', 'wpshadow' ),
		'category' => 'design',
		'level' => 'intermediate',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/customize-live-customizer',
	],
	[
		'id' => 'nav-appearance-menus',
		'selector' => '#menu-appearance li a[href*="nav-menus.php"]',
		'title' => __( 'Edit menus', 'wpshadow' ),
		'message' => __( 'Create and organize navigation menus for your site.', 'wpshadow' ),
		'category' => 'design',
		'level' => 'intermediate',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/menus-edit-menus',
	],
	[
		'id' => 'nav-appearance-widgets',
		'selector' => '#menu-appearance li a[href*="widgets.php"]',
		'title' => __( 'Manage widgets', 'wpshadow' ),
		'message' => __( 'Add and arrange widgets in your site sidebar and other areas.', 'wpshadow' ),
		'category' => 'design',
		'level' => 'intermediate',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/widgets-manage-widgets',
	],
	[
		'id' => 'theme-activate',
		'selector' => '.theme-actions .activate-theme, .activate a',
		'title' => __( 'Activate theme', 'wpshadow' ),
		'message' => __( 'Set this as your active theme and apply it to your site.', 'wpshadow' ),
		'category' => 'design',
		'level' => 'beginner',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/general-activate-theme',
	],
	[
		'id' => 'theme-customize',
		'selector' => '.theme-actions .customize-theme, .customize a',
		'title' => __( 'Live preview', 'wpshadow' ),
		'message' => __( 'Preview changes to this theme in real-time before applying.', 'wpshadow' ),
		'category' => 'design',
		'level' => 'beginner',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/general-live-preview',
	],
	[
		'id' => 'page-template',
		'selector' => 'select[name="page_template"], .editor-page-attributes__template select',
		'title' => __( 'Page template', 'wpshadow' ),
		'message' => __( 'Choose a custom layout template for this page if available.', 'wpshadow' ),
		'category' => 'design',
		'level' => 'intermediate',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/general-page-template',
	],
	[
		'id' => 'nav-appearance-theme-editor',
		'selector' => '#menu-appearance li a[href*="theme-editor.php"]',
		'title' => __( 'Theme File Editor', 'wpshadow' ),
		'message' => __( 'Edit theme template and CSS files directly. Be careful—errors can break your site. Consider using a child theme instead.', 'wpshadow' ),
		'category' => 'design',
		'level' => 'intermediate',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/theme-editor-theme-file-editor',
	],
	[
		'id' => 'nav-appearance-site-editor',
		'selector' => '#menu-appearance li a[href*="site-editor.php"], #menu-appearance li a[href*="customize.php"]',
		'title' => __( 'Site/Theme Editor', 'wpshadow' ),
		'message' => __( 'Edit your site design, templates, and styles with a visual editor. Make changes with live preview before publishing.', 'wpshadow' ),
		'category' => 'design',
		'level' => 'intermediate',
		'kb_url' => 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/kb/customize-site-theme-editor',
	],
];
