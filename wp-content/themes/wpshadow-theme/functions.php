<?php
/**
 * WPShadow Theme functions and definitions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPSHADOW_THEME_VERSION' ) ) {
	$theme = wp_get_theme();
	define( 'WPSHADOW_THEME_VERSION', $theme->get( 'Version' ) );
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 64, 'width' => 64, 'flex-height' => true, 'flex-width' => true ) );
	register_nav_menus( array( 'primary' => __( 'Primary Menu', 'wpshadow-theme' ) ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	// Typography chosen to feel purposeful and modern.
	wp_enqueue_style( 'wpshadow-theme-fonts', 'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap', array(), null );
	wp_enqueue_style( 'wpshadow-theme', get_template_directory_uri() . '/assets/css/theme.css', array( 'wpshadow-theme-fonts' ), WPSHADOW_THEME_VERSION );
} );

/**
 * Register a simple sidebar.
 */
add_action( 'widgets_init', function () {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'wpshadow-theme' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<section id="%1$s" class="widget %2$s card">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
} );
