<?php
/**
 * Plugin Name: Lakeview Brands WordPress Tools
 * Description: Our client tool for optimzing WordPress to help your website.
 * Version:     1.251226
 * Author:      Lakeview Brands
 * Text Domain: lakeview-brands
 * License:     GPL-2.0+
 *
 * @package Lakeview_Brands_SEO
 */



define( 'LVB_VERSION', '1.251226' ); // Bump Version
define( 'LVB_PATH', plugin_dir_path( __FILE__ ) );
define( 'LVB_URL', plugin_dir_url( __FILE__ ) );

// Include Core Classes
require_once LVB_PATH . 'includes/class-lvb-data.php';
require_once LVB_PATH . 'includes/class-lvb-admin.php';
require_once LVB_PATH . 'includes/class-lvb-rest.php';
require_once LVB_PATH . 'includes/class-lvb-linker.php';
require_once LVB_PATH . 'includes/class-lvb-auto-tagger.php';
require_once LVB_PATH . 'includes/class-lvb-shortcodes.php';
require_once LVB_PATH . 'includes/class-lvb-cleanup.php';

function LVB_init() {
	$admin = new LVB_Admin();
	$admin->init();

	$rest = new LVB_Rest();
	$rest->init();

	$linker = new LVB_Linker();
	$linker->init();


    // <--- NEW: Initialize Tagger
	$tagger = new LVB_Auto_Tagger();
	$tagger->init();
}
add_action( 'plugins_loaded', 'LVB_init' );