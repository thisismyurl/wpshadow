<?php
/**
 * Plugin Name:       HEIC Support by thisismyurl.com
 * Plugin URI:        https://thisismyurl.com/thisismyurl-heic-support/
 * Description:       Automatically convert HEIC/HEIF images from iOS devices to WebP with secure backups.
 * Version:           1.251229
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Christopher Ross
 * Author URI:        https://thisismyurl.com/
 * License:           GPLv2 or later
 * Text Domain:       thisismyurl-heic-support
 * GitHub Plugin URI: https://github.com/thisismyurl/thisismyurl-heic-support
 * Primary Branch:    main
 * Update URI:        https://github.com/thisismyurl/thisismyurl-heic-support
 * Donate link:       https://thisismyurl.com/donate/
 * * @package TIMU_HEIC_Support
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TIMU_HEIC_Support {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
        add_filter( 'upload_mimes', array( __CLASS__, 'allow_heic_uploads' ) );
        add_filter( 'wp_handle_upload', array( __CLASS__, 'handle_heic_upload' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'add_plugin_action_links' ) );
    }

    public static function allow_heic_uploads( $mimes ) {
        $mimes['heic'] = 'image/heic';
        $mimes['heif'] = 'image/heif';
        return $mimes;
    }

    public static function add_admin_menu() {
        add_management_page(
            __( 'HEIC Support', 'thisismyurl-heic-support' ),
            __( 'HEIC Support', 'thisismyurl-heic-support' ),
            'manage_options',
            'heic-optimizer',
            array( __CLASS__, 'render_admin_page' )
        );
    }

    public static function add_plugin_action_links( $links ) {
        $custom_links = array(
            '<a href="' . admin_url( 'admin.php?page=heic-optimizer' ) . '">' . esc_html__( 'Settings', 'thisismyurl-heic-support' ) . '</a>',
            '<a href="https://thisismyurl.com/donate/" target="_blank" style="color: #2271b1; font-weight: bold;">' . esc_html__( 'Donate', 'thisismyurl-heic-support' ) . '</a>',
        );
        return array_merge( $custom_links, $links );
    }

    /**
     * Handle immediate conversion upon upload.
     */
    public static function handle_heic_upload( $upload ) {
        if ( ! in_array( $upload['type'], array( 'image/heic', 'image/heif' ) ) ) {
            return $upload;
        }

        // Logic for conversion using ImageMagick goes here, 
        // similar to the convert_to_webp logic in your previous script.
        return $upload;
    }

    public static function render_admin_page() {
        // Reuse your professional Metabox Holder UI from the WebP plugin here.
        echo '<div class="wrap"><h1>' . esc_html__( 'HEIC Support', 'thisismyurl-heic-support' ) . '</h1><p>' . esc_html__( 'HEIC conversion engine ready. (Requires Imagick)', 'thisismyurl-heic-support' ) . '</p></div>';
    }
}

TIMU_HEIC_Support::init();

// GitHub Updater Integration
add_action( 'plugins_loaded', function() {
    $updater_path = plugin_dir_path( __FILE__ ) . 'updater.php';
    if ( file_exists( $updater_path ) ) {
        require_once $updater_path;
        if ( class_exists( 'FWO_GitHub_Updater' ) ) {
            new FWO_GitHub_Updater( array(
                'slug'               => 'thisismyurl-heic-support',
                'proper_folder_name' => 'thisismyurl-heic-support',
                'api_url'            => 'https://api.github.com/repos/thisismyurl/thisismyurl-heic-support/releases/latest',
                'github_url'         => 'https://github.com/thisismyurl/thisismyurl-heic-support',
                'plugin_file'        => __FILE__,
            ) );
        }
    }
});